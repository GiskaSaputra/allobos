<?php

namespace App\Http\Controllers\Signing;

use App\Enums\CertificateStatus;
use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Program;
use App\Models\ProgramSigner;
use App\Models\SignatureSpecimen;
use App\Services\Certificates\CertificateBuilder;
use App\Services\Certificates\SigningService;
use App\Services\Chain\ChainClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class SigningController extends Controller
{
    public function __construct(private readonly SigningService $signing) {}

    public function index(Request $request): View
    {
        return view('signing.index', [
            'signers' => ProgramSigner::query()->with('program')->where('user_id', $request->user()->id)->latest()->get()
                ->map(fn ($s) => [
                    'signer' => $s,
                    'pending' => $s->program->certificates()->where('status', CertificateStatus::MenungguTandaTangan->value)->count(),
                    'total' => $s->program->certificates()->where('status', '!=', CertificateStatus::Dibatalkan->value)->count(),
                ]),
            'hasSpecimen' => SignatureSpecimen::query()->where('user_id', $request->user()->id)->exists(),
        ]);
    }

    public function show(Request $request, Program $program): View|RedirectResponse
    {
        $signer = $this->signing->signerFor($program, $request->user());
        abort_unless($signer, 403);

        if (SignatureSpecimen::query()->where('user_id', $request->user()->id)->doesntExist()) {
            session(['url.intended' => route('signing.show', $program)]);

            return redirect()->route('signing.specimen.edit')->with('status', 'Simpan spesimen tanda tanganmu dulu, lalu kamu akan kembali ke kelas ini.');
        }

        return view('signing.show', [
            'program' => $program->load(['signers.user', 'sessions', 'field']),
            'signer' => $signer,
            'certificates' => $program->certificates()->with('user')->whereIn('status', [CertificateStatus::MenungguTandaTangan->value, CertificateStatus::Dibatalkan->value])->orderBy('recipient_name')->get(),
            'signedCount' => $program->certificates()->whereNotIn('status', [CertificateStatus::MenungguTandaTangan->value, CertificateStatus::Dibatalkan->value])->count(),
            'walletMessage' => $this->signing->walletMessage($program),
        ]);
    }

    public function sign(Request $request, Program $program): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']], [
            'password.required' => 'Masukkan kata sandimu untuk menandatangani.',
            'password.current_password' => 'Kata sandi salah. Tanda tangan tidak bisa diwakilkan.',
        ]);

        try {
            $this->signing->sign($program, $request->user(), (string) $request->ip());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $waiting = $program->signers()->whereNull('signed_at')->exists();

        return redirect()->route('signing.index')->with('status', $waiting
            ? 'Tanda tanganmu tersimpan. Sertifikat terbit setelah penanda tangan lain menandatangani.'
            : 'Sertifikat ditandatangani. PDF disusun dan sidik digitalnya dicatat ke blockchain.');
    }

    public function exclude(Request $request, Program $program, Certificate $certificate): RedirectResponse
    {
        $reason = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:300']])['reason'];

        try {
            $this->signing->exclude($program, $certificate, $request->user(), $reason);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', $certificate->recipient_name.' dikeluarkan dari daftar penerima.');
    }

    public function preview(Request $request, Program $program, Certificate $certificate): View
    {
        abort_unless($this->signing->signerFor($program, $request->user()) && $certificate->program_id === $program->id, 403);

        return view('pdf.certificate', app(CertificateBuilder::class)->viewData($certificate, preview: true, withSignatures: false));
    }

    public function wallet(Request $request, Program $program, ChainClient $chain): JsonResponse
    {
        $data = $request->validate(['address' => ['required', 'regex:/^0x[a-fA-F0-9]{40}$/'], 'signature' => ['required', 'regex:/^0x[a-fA-F0-9]+$/']]);

        try {
            $this->signing->attachWallet($program, $request->user(), $data['address'], $data['signature'], $chain);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Tanda tangan dompet tersimpan sebagai bukti tambahan.']);
    }
}
