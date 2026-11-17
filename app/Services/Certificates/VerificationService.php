<?php

namespace App\Services\Certificates;

use App\Enums\CertificateStatus;
use App\Enums\RoleName;
use App\Models\Certificate;
use App\Models\User;
use App\Notifications\Pemberitahuan;
use App\Services\Chain\ChainClient;
use App\Services\Chain\ChainException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

/** Membandingkan data di server dengan catatan kontrak pintar (SR-09, SR-10). */
class VerificationService
{
    public const ASLI = 'asli';

    public const DICABUT = 'dicabut';

    public const BELUM = 'belum_tercatat';

    public const TIDAK_COCOK = 'tidak_cocok';

    public const TIDAK_DITEMUKAN = 'tidak_ditemukan';

    public function __construct(
        private readonly ChainClient $chain,
        private readonly CertificateHasher $hasher,
    ) {}

    /** @return array{status:string,certificate:?Certificate,chain:?array,chain_error:?string,hash:?string} */
    public function check(string $code): array
    {
        $certificate = Certificate::query()->with(['program.field', 'program.signers.user', 'transaction', 'revokeTransaction'])->where('code', strtoupper(trim($code)))->first();
        $visible = [CertificateStatus::MenungguPencatatan, CertificateStatus::Tercatat, CertificateStatus::Dicabut];

        if (! $certificate || ! in_array($certificate->status, $visible, true)) {
            return ['status' => self::TIDAK_DITEMUKAN, 'certificate' => null, 'chain' => null, 'chain_error' => null, 'hash' => null];
        }

        $hash = $this->hasher->hash($certificate);
        $base = ['certificate' => $certificate, 'hash' => $hash, 'chain' => null, 'chain_error' => null];

        if ($certificate->status === CertificateStatus::MenungguPencatatan) {
            return ['status' => self::BELUM] + $base;
        }

        try {
            $record = $this->chain->verify($certificate->hash);
        } catch (ChainException $e) {
            report($e);

            return ['status' => $certificate->status === CertificateStatus::Dicabut ? self::DICABUT : self::ASLI, 'chain_error' => 'Jaringan blockchain sedang tidak bisa dihubungi, jadi status ini diambil dari server alloBos. Coba periksa lagi nanti.'] + $base;
        }

        $base['chain'] = $record;
        if ($hash !== $certificate->hash || ! $record['exists']) {
            $this->alertMismatch($certificate);

            return ['status' => self::TIDAK_COCOK] + $base;
        }
        if ($record['revoked'] || $certificate->status === CertificateStatus::Dicabut) {
            return ['status' => self::DICABUT] + $base;
        }

        return ['status' => self::ASLI] + $base;
    }

    private function alertMismatch(Certificate $certificate): void
    {
        if (Cache::add('sertifikat.tidak-cocok.'.$certificate->id, true, now()->addDay())) {
            Notification::send(User::role(RoleName::Admin->value)->get(), new Pemberitahuan(
                'Sertifikat tidak cocok dengan blockchain',
                'Sidik digital sertifikat '.$certificate->code.' berbeda dengan catatan kontrak pintar. Periksa log audit.',
                route('certificates.verify', $certificate->code),
            ));
        }
    }
}
