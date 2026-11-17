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
        $certificate->loadMissing(['program.field', 'program.sessions', 'program.signers.user']);

        $signers = $certificate->program->signers->sortBy('sort')->map(function ($signer) {
            $path = SignatureSpecimen::query()->where('user_id', $signer->user_id)->value('path');

            return [
                'name' => $signer->user->name,
                'title' => $signer->title,
                'image' => $path && Storage::disk('local')->exists($path) ? 'data:image/png;base64,'.base64_encode(Storage::disk('local')->get($path)) : null,
            ];
        })->values();

        $qr = Builder::create()->writer(new PngWriter)->data($certificate->verifyUrl())->size(360)->margin(16)->build();

        $pdf = Pdf::loadView('pdf.certificate', [
            'certificate' => $certificate,
            'program' => $certificate->program,
            'signers' => $signers,
            'qr' => $qr->getDataUri(),
        ])->setPaper('a4', 'landscape');

        $path = 'sertifikat/'.$certificate->code.'.pdf';
        Storage::disk('local')->put($path, $pdf->output());
        $certificate->update(['pdf_path' => $path]);

        return $path;
    }
}
