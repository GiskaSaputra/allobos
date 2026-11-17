<?php

namespace App\Http\Controllers\Warga;

use App\Enums\CertificateStatus;
use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Services\Certificates\CertificateBuilder;
use App\Services\Chain\ChainClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateController extends Controller
{
    public function index(Request $request): View
    {
        return view('warga.certificates.index', [
            'certificates' => Certificate::query()->with('program')->where('user_id', $request->user()->id)
                ->where('status', '!=', CertificateStatus::Dibatalkan->value)->latest()->get(),
        ]);
    }

    public function show(Request $request, Certificate $certificate, ChainClient $chain): View
    {
        Gate::authorize('view', $certificate);

        return view('warga.certificates.show', [
            'certificate' => $certificate->load(['program.field', 'program.signers.user', 'transaction']),
            'hasShop' => $request->user()->shop()->exists(),
            'explorer' => $chain->explorerTx($certificate->transaction?->tx_hash),
        ]);
    }

    /** Pratinjau sertifikat di halaman, sama dengan PDF. Tanda tangan tampil setelah sertifikat sah. */
    public function preview(Certificate $certificate, CertificateBuilder $builder): View
    {
        Gate::authorize('view', $certificate);

        return view('pdf.certificate', $builder->viewData($certificate, preview: true, withSignatures: $certificate->status->downloadable()));
    }

    public function pdf(Certificate $certificate): StreamedResponse|RedirectResponse
    {
        Gate::authorize('view', $certificate);

        if (! $certificate->status->downloadable() || ! $certificate->pdf_path || ! Storage::disk('local')->exists($certificate->pdf_path)) {
            return back()->with('error', 'PDF sertifikat belum siap. Sertifikat bisa diunduh setelah ditandatangani dan disusun.');
        }

        return Storage::disk('local')->download($certificate->pdf_path, 'sertifikat-'.$certificate->code.'.pdf');
    }

    public function update(Request $request, Certificate $certificate): RedirectResponse
    {
        Gate::authorize('view', $certificate);
        $data = $request->validate(['show_full_name' => ['sometimes', 'boolean'], 'show_on_shop' => ['sometimes', 'boolean']]);

        if (($data['show_on_shop'] ?? false) && ($certificate->status !== CertificateStatus::Tercatat || ! $request->user()->shop()->exists())) {
            return back()->with('error', 'Lencana hanya bisa tampil di lapak untuk sertifikat Tercatat dan bila kamu sudah punya lapak.');
        }

        $certificate->update($data);

        return back()->with('status', 'Pengaturan sertifikat disimpan.');
    }
}
