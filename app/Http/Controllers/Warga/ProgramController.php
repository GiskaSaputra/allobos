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
use App\Services\Payments\PaymentService;
use App\Services\Programs\ProgramService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class ProgramController extends Controller
{
    public function __construct(
        private readonly ProgramService $programs,
        private readonly PaymentService $payments,
    ) {}

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

    /** Halaman konfirmasi sebelum mendaftar: jadwal, lokasi, biaya, dan persetujuan. */
    public function confirm(Request $request, Program $program): View|RedirectResponse
    {
        $enrollment = Enrollment::query()->where('program_id', $program->id)->where('user_id', $request->user()->id)->first();
        if ($enrollment && in_array($enrollment->status->value, EnrollmentStatus::participating(), true)) {
            return redirect()->route('programs.show', $program)->with('status', 'Kamu sudah terdaftar di kelas ini.');
        }

        return view('warga.programs.confirm', [
            'program' => $program->load(['sessions', 'field', 'speakers']),
            'pending' => $enrollment?->status === EnrollmentStatus::MenungguBayar ? $enrollment->latestPayment : null,
        ]);
    }

    public function enroll(Request $request, Program $program): RedirectResponse
    {
        $request->validate(['agree' => ['accepted']], ['agree.accepted' => 'Centang konfirmasi dulu supaya pendaftaran bisa diproses.']);

        try {
            $enrollment = $this->programs->enroll($request->user(), $program);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($enrollment->status === EnrollmentStatus::MenungguBayar) {
            try {
                $payment = $this->payments->start($enrollment->load('program'));
            } catch (RuntimeException $e) {
                $this->programs->release($enrollment);

                return back()->with('error', $e->getMessage());
            }

            return redirect()->away($payment->payment_url);
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
