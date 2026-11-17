<?php

namespace App\Http\Controllers\Warga;

use App\Enums\EnrollmentStatus;
use App\Enums\ProgramStatus;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\Program;
use App\Models\ProgramSession;
use App\Services\Programs\ProgramService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class ProgramController extends Controller
{
    public function __construct(private readonly ProgramService $programs) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $enrollments = Enrollment::query()->with(['program.sessions', 'program.field'])->where('user_id', $user->id)
            ->where('status', '!=', EnrollmentStatus::Batal->value)->latest()->get();

        return view('warga.programs.index', [
            'enrollments' => $enrollments->map(fn ($e) => [
                'enrollment' => $e,
                'program' => $e->program,
                'progress' => $this->programs->progress($user, $e->program),
                'running' => $e->program->sessions->first(fn ($s) => $s->isRunning()),
                'attended' => Attendance::query()->where('user_id', $user->id)->whereIn('program_session_id', $e->program->sessions->pluck('id'))->pluck('program_session_id')->all(),
            ]),
            'certificates' => Certificate::query()->where('user_id', $user->id)->pluck('id', 'program_id'),
            'open' => Program::query()->with(['sessions', 'field'])->where('status', ProgramStatus::Dibuka->value)
                ->whereNotIn('id', $enrollments->pluck('program_id'))
                ->orderByRaw('case when business_field_id in ('.implode(',', array_map('intval', $user->profile->interests ?: [0])).') then 0 else 1 end')
                ->limit(4)->get(),
        ]);
    }

    public function enroll(Request $request, Program $program): RedirectResponse
    {
        try {
            $this->programs->enroll($request->user(), $program);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('warga.programs.index')->with('status', 'Kamu terdaftar di '.$program->title.'.');
    }

    public function cancel(Request $request, Program $program): RedirectResponse
    {
        try {
            $this->programs->cancel($request->user(), $program);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Pendaftaran kelas dibatalkan.');
    }

    public function attend(Request $request, ProgramSession $session): RedirectResponse
    {
        $code = $request->validate(['code' => ['required', 'string', 'max:12']], ['code.required' => 'Masukkan kode kehadiran dari ruang kelas.'])['code'];

        try {
            $this->programs->attend($request->user(), $session, $code);
        } catch (RuntimeException $e) {
            return back()->withErrors(['code_'.$session->id => $e->getMessage()]);
        }

        return back()->with('status', 'Kehadiranmu di '.$session->title.' tercatat.');
    }

    public function material(Request $request, Program $program, int $index): RedirectResponse
    {
        $material = $program->materials[$index] ?? abort(404);
        $this->programs->openMaterial($request->user(), $program, $index);

        return redirect()->away($material['url']);
    }
}
