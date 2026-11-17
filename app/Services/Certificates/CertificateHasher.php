<?php

namespace App\Services\Certificates;

use App\Models\Certificate;
use Illuminate\Support\Str;

/** Sidik digital SHA-256 dari data kanonik sertifikat (SR-06). Nama penerima hanya masuk sebagai sidik bergaram. */
class CertificateHasher
{
    public function canonical(Certificate $certificate): string
    {
        $certificate->loadMissing('program.signers.user');

        return json_encode([
            'code' => $certificate->code,
            'program_id' => $certificate->program_id,
            'issued_on' => $certificate->issued_on?->format('Y-m-d'),
            'signers' => $certificate->program->signers->sortBy('sort')->map(fn ($s) => $s->user->name)->values()->all(),
            'recipient' => hash('sha256', config('chain.salt').'|'.Str::of($certificate->recipient_name)->squish()->lower()),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function hash(Certificate $certificate): string
    {
        return '0x'.hash('sha256', $this->canonical($certificate));
    }

    public function classId(int $programId): string
    {
        return '0x'.hash('sha256', 'program:'.$programId.'|'.config('chain.salt'));
    }
}
