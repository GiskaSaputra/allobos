<?php

namespace App\Services\Certificates;

use App\Models\Certificate;
use App\Models\SignatureSpecimen;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

class CertificateBuilder
{
    public function build(Certificate $certificate): string
    {
        $pdf = Pdf::loadView('pdf.certificate', $this->viewData($certificate))->setPaper('a4', 'landscape');

        $path = 'sertifikat/'.$certificate->code.'.pdf';
        Storage::disk('local')->put($path, $pdf->output());
        $certificate->update(['pdf_path' => $path]);

        return $path;
    }

    /**
     * Data templat sertifikat. PDF memakai gambar tertanam (data URI) karena dompdf tidak mengambil berkas lewat jaringan,
     * pratinjau di peramban memakai alamat aset biasa supaya ringan.
     *
     * @param  bool  $withSignatures  false untuk pratinjau penanda tangan sebelum menandatangani
     */
    public function viewData(Certificate $certificate, bool $preview = false, bool $withSignatures = true): array
    {
        $certificate->loadMissing(['program.field', 'program.sessions', 'program.signers.user']);

        $signers = $certificate->program->signers->sortBy('sort')->map(function ($signer) use ($withSignatures) {
            $path = $withSignatures ? SignatureSpecimen::query()->where('user_id', $signer->user_id)->value('path') : null;

            return [
                'name' => $signer->user->name,
                'title' => $signer->title,
                'image' => $path && Storage::disk('local')->exists($path) ? 'data:image/png;base64,'.base64_encode(Storage::disk('local')->get($path)) : null,
            ];
        })->values();

        $image = fn (string $file) => $preview
            ? asset("image/{$file}")
            : 'data:image/png;base64,'.base64_encode((string) file_get_contents(public_path("image/{$file}")));

        return [
            'certificate' => $certificate,
            'program' => $certificate->program,
            'signers' => $signers,
            'qr' => Builder::create()->writer(new PngWriter)->data($certificate->verifyUrl())->size(360)->margin(8)->build()->getDataUri(),
            'background' => $image('sertifikat-latar.png'),
            'logo' => $image('logo-allobos-480.png'),
            'preview' => $preview,
        ];
    }
}
