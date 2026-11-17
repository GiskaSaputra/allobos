<?php

namespace App\Http\Controllers\Pengelola;

use App\Enums\EnrollmentStatus;
use App\Enums\ProgramKind;
use App\Enums\ProgramStatus;
use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pengelola\ProgramRequest;
use App\Models\Attendance;
use App\Models\BusinessField;
use App\Models\Program;
use App\Models\ProgramSession;
use App\Models\Speaker;
use App\Models\User;
use App\Services\Programs\ProgramService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProgramController extends Controller
{
    public function __construct(private readonly ProgramService $programs) {}

    public function index(Request $request): View
    {
        $request->validate(['status' => ['nullable', Rule::enum(ProgramStatus::class)]]);

        return view('pengelola.programs.index', [
            'programs' => Program::query()->with(['sessions', 'field'])->withCount(['enrollments' => fn ($q) => $q->where('status', '!=', EnrollmentStatus::Batal->value)])
                ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
                ->when($request->query('q'), fn ($q, $term) => $q->where('title', 'like', '%'.$term.'%'))
                ->latest()->paginate(20)->withQueryString(),
            'status' => $request->query('status'),
        ]);
    }

    public function create(): View
    {
        return $this->form(new Program(['kind' => ProgramKind::Workshop, 'quota' => 30, 'is_official' => true]));
    }

    public function store(ProgramRequest $request): RedirectResponse
    {
        $program = DB::transaction(function () use ($request) {
            $program = Program::query()->create($this->attributes($request) + [
                'slug' => $this->slug($request->string('title')),
                'status' => ProgramStatus::Draf,
                'created_by' => $request->user()->id,
            ]);
            $this->syncRelations($program, $request);

            return $program;
        });

        return redirect()->route('pengelola.programs.show', $program)->with('status', 'Kelas disimpan sebagai Draf. Terbitkan bila sudah siap.');
    }

    public function show(Program $program): View
    {
        $program->load(['sessions', 'speakers.user', 'signers.user', 'field']);

        return view('pengelola.programs.show', [
            'program' => $program,
            'enrolled' => $program->enrollments()->where('status', '!=', EnrollmentStatus::Batal->value)->count(),
            'certificates' => $program->certificates()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'signerOptions' => $this->signerOptions($program),
        ]);
    }

    public function edit(Program $program): View
    {
        return $this->form($program->load(['sessions', 'speakers']));
    }

    public function update(ProgramRequest $request, Program $program): RedirectResponse
    {
        if ($program->status === ProgramStatus::Selesai) {
            return back()->with('error', 'Kelas yang sudah ditutup tidak bisa diubah.');
        }

        DB::transaction(function () use ($request, $program) {
            $program->update($this->attributes($request));
            $this->syncRelations($program, $request);
        });
        $this->programs->syncStatus($program->fresh());

        return redirect()->route('pengelola.programs.show', $program)->with('status', 'Kelas diperbarui.');
    }

    public function destroy(Program $program): RedirectResponse
    {
        if ($program->status !== ProgramStatus::Draf) {
            return back()->with('error', 'Hanya kelas berstatus Draf yang bisa dihapus.');
        }
        $program->delete();

        return redirect()->route('pengelola.programs.index')->with('status', 'Kelas dihapus.');
    }

    public function publish(Program $program): RedirectResponse
    {
        return $this->act(fn () => $this->programs->publish($program), 'Kelas diterbitkan dan tampil di halaman publik.');
    }

    public function close(Program $program): RedirectResponse
    {
        try {
            $count = $this->programs->close($program);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', "Kelas ditutup. {$count} draf sertifikat dibuat dan penanda tangan sudah diberi tahu.");
    }

    public function participants(Program $program): View
    {
        $program->load('sessions');
        $attendance = Attendance::query()->whereIn('program_session_id', $program->sessions->pluck('id'))->get()->groupBy('user_id');

        return view('pengelola.programs.participants', [
            'program' => $program,
            'rows' => $program->enrollments()->with('user')->latest()->get()->map(fn ($e) => [
                'enrollment' => $e,
                'attended' => $attendance->get($e->user_id)?->count() ?? 0,
            ]),
        ]);
    }

    public function export(Program $program): StreamedResponse
    {
        $program->load('sessions');
        $attendance = Attendance::query()->whereIn('program_session_id', $program->sessions->pluck('id'))->get()->groupBy('user_id');
        $sheet = (new Spreadsheet)->getActiveSheet();
        $sheet->setTitle('Peserta');
        $sheet->fromArray(['No', 'Nama', 'Email', 'WhatsApp', 'Status', 'Sesi dihadiri', 'Jumlah sesi', 'Terdaftar'], null, 'A1');

        foreach ($program->enrollments()->with('user')->get()->values() as $i => $e) {
            $sheet->fromArray([$i + 1, $e->user->name, $e->user->email, $e->user->whatsapp, $e->status->label(), $attendance->get($e->user_id)?->count() ?? 0, $program->sessions->count(), $e->created_at->format('Y-m-d H:i')], null, 'A'.($i + 2));
        }
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return response()->streamDownload(fn () => (new Xlsx($sheet->getParent()))->save('php://output'), 'peserta-'.$program->slug.'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function code(ProgramSession $session): View
    {
        $code = $this->programs->openCode($session);

        return view('pengelola.programs.code', ['session' => $session->load('program'), 'code' => $code]);
    }

    public function signers(Request $request, Program $program): RedirectResponse
    {
        $data = $request->validate([
            'signers' => ['required', 'array', 'min:1', 'max:2'],
            'signers.*.user_id' => ['required', 'distinct', 'exists:users,id'],
            'signers.*.title' => ['required', 'string', 'max:120'],
        ], ['signers.required' => 'Pilih minimal satu penanda tangan.']);

        return $this->act(fn () => $this->programs->setSigners($program, array_values($data['signers'])), 'Penanda tangan sertifikat ditetapkan.');
    }

    private function form(Program $program): View
    {
        return view('pengelola.programs.form', [
            'program' => $program,
            'fields' => BusinessField::query()->ordered()->pluck('name', 'id'),
            'speakers' => Speaker::query()->orderBy('name')->get(),
            'sessions' => old('sessions', $program->sessions->map(fn ($s) => ['title' => $s->title, 'date' => $s->starts_at->format('Y-m-d'), 'start' => $s->starts_at->format('H:i'), 'end' => $s->ends_at->format('H:i')])->all() ?: [['title' => 'Sesi 1', 'date' => now()->addWeek()->format('Y-m-d'), 'start' => '09:00', 'end' => '12:00']]),
        ]);
    }

    private function attributes(ProgramRequest $request): array
    {
        return $request->safe()->only(['title', 'kind', 'business_field_id', 'description', 'location', 'quota']) + [
            'is_official' => $request->boolean('is_official'),
            'is_demo' => $request->boolean('is_demo'),
            'materials' => $request->materials(),
        ];
    }

    /** Jadwal ditulis ulang hanya bila belum ada kehadiran supaya catatan hadir tidak hilang. */
    private function syncRelations(Program $program, ProgramRequest $request): void
    {
        $program->speakers()->sync($request->input('speakers', []));

        $hasAttendance = Attendance::query()->whereIn('program_session_id', $program->sessions()->pluck('id'))->exists();
        if ($hasAttendance) {
            return;
        }
        $program->sessions()->delete();
        foreach ($request->validated('sessions') as $s) {
            $program->sessions()->create([
                'title' => $s['title'],
                'starts_at' => Carbon::parse($s['date'].' '.$s['start']),
                'ends_at' => Carbon::parse($s['date'].' '.$s['end']),
            ]);
        }
    }

    private function signerOptions(Program $program)
    {
        $speakerBosses = User::query()->whereIn('id', $program->speakers->pluck('user_id')->filter())->get()->map(fn ($u) => ['id' => $u->id, 'label' => $u->name.' (pembicara)', 'title' => 'Pembicara kelas']);
        $leaders = User::role([RoleName::Pengelola->value, RoleName::Admin->value])->orderBy('name')->get()->map(fn ($u) => ['id' => $u->id, 'label' => $u->name.' (pimpinan IPI)', 'title' => 'Pimpinan Institusi Pengusaha Indonesia']);

        return $speakerBosses->concat($leaders)->unique('id')->values();
    }

    private function slug(string $title): string
    {
        $base = Str::slug($title) ?: 'kelas';
        $slug = $base;
        for ($i = 2; Program::query()->where('slug', $slug)->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }

    private function act(callable $action, string $message): RedirectResponse
    {
        try {
            $action();
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', $message);
    }
}
