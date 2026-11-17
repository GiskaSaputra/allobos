<?php

namespace App\Services\Certificates;

use App\Enums\CertificateStatus;
use App\Enums\RoleName;
use App\Models\Certificate;
use App\Models\ChainTransaction;
use App\Models\Program;
use App\Models\User;
use App\Notifications\Pemberitahuan;
use App\Services\Chain\ChainClient;
use App\Services\Readiness\ReadinessCalculator;
use Illuminate\Support\Facades\Notification;

class AnchorService
{
    public function __construct(
        private readonly ChainClient $chain,
        private readonly CertificateHasher $hasher,
        private readonly CertificateBuilder $builder,
        private readonly ReadinessCalculator $readiness,
    ) {}

    /** PDF dan sidik digital untuk sertifikat yang sudah lengkap ditandatangani. */
    public function prepare(Program $program): int
    {
        $certificates = $program->certificates()->where('status', CertificateStatus::Ditandatangani->value)->get();

        foreach ($certificates as $certificate) {
            $certificate->update(['hash' => $this->hasher->hash($certificate)]);
            $this->builder->build($certificate);
            $certificate->update(['status' => CertificateStatus::MenungguPencatatan]);
        }

        return $certificates->count();
    }

    /** Satu transaksi untuk satu kelas (SR-07). Melempar ChainException bila jaringan gagal. */
    public function anchor(Program $program): ?ChainTransaction
    {
        $certificates = $program->certificates()->with('user')->where('status', CertificateStatus::MenungguPencatatan->value)->whereNotNull('hash')->get();
        if ($certificates->isEmpty()) {
            return null;
        }

        $tx = ChainTransaction::query()->firstOrCreate(
            ['kind' => 'issue', 'program_id' => $program->id, 'status' => 'dikirim'],
            ['chain_name' => config('chain.name')],
        );
        $tx->increment('attempts');

        try {
            $result = $this->chain->issueBatch($certificates->pluck('hash')->all(), $this->hasher->classId($program->id));
        } catch (\Throwable $e) {
            $tx->update(['error' => mb_substr($e->getMessage(), 0, 1000)]);
            throw $e;
        }

        // Bila semua sidik sudah tercatat di percobaan sebelumnya, bukti diambil dari peristiwa yang ada.
        $proof = $result['transaction'] ?? collect($result['existing'])->filter()->first() ?? [];
        $tx->update([
            'tx_hash' => $proof['tx_hash'] ?? null,
            'block_number' => $proof['block_number'] ?? null,
            'status' => 'terkonfirmasi',
            'error' => null,
        ]);

        foreach ($certificates as $certificate) {
            $certificate->update([
                'status' => CertificateStatus::Tercatat,
                'chain_transaction_id' => $tx->id,
                'contract_address' => $result['contract'],
            ]);
            $this->chain->forget($certificate->hash);

            $url = route('warga.certificates.show', $certificate);
            $certificate->user->notify(new Pemberitahuan('Sertifikat tercatat dan siap diunduh', 'Sertifikat kelas '.$program->title.' sudah tercatat di blockchain dan bisa diperiksa siapa saja.', $url, mail: true));
            $this->readiness->record($certificate->user, 'sertifikat', 'Sertifikat tercatat: '.$program->title, $url);
        }

        return $tx;
    }

    public function anchorFailed(Program $program, string $error): void
    {
        ChainTransaction::query()->where('kind', 'issue')->where('program_id', $program->id)->where('status', 'dikirim')
            ->update(['status' => 'gagal', 'error' => mb_substr($error, 0, 1000)]);

        Notification::send(
            User::role([RoleName::Admin->value, RoleName::Pengelola->value])->get(),
            new Pemberitahuan('Pencatatan blockchain gagal', 'Sertifikat kelas '.$program->title.' belum tercatat. Tekan Catat Ulang setelah jaringan atau saldo dompet penerbit diperiksa.', route('pengelola.certificates.index', ['kelas' => $program->id])),
        );
    }

    public function revoke(Certificate $certificate): void
    {
        if (! $certificate->hash) {
            return;
        }
        $tx = ChainTransaction::query()->create(['kind' => 'revoke', 'program_id' => $certificate->program_id, 'chain_name' => config('chain.name'), 'status' => 'dikirim', 'attempts' => 1]);

        try {
            $result = $this->chain->revoke($certificate->hash, (string) $certificate->reason);
        } catch (\Throwable $e) {
            $tx->update(['status' => 'gagal', 'error' => mb_substr($e->getMessage(), 0, 1000)]);
            throw $e;
        }

        $tx->update(['tx_hash' => $result['tx_hash'] ?? null, 'block_number' => $result['block_number'] ?? null, 'status' => 'terkonfirmasi']);
        $certificate->update(['revoke_transaction_id' => $tx->id]);
        $this->chain->forget($certificate->hash);
    }
}
