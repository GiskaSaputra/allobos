<?php

namespace App\Services\Chain;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;

/** Satu pintu ke kontrak AlloBosCertificate. Tulis dan baca lewat skrip ethers.js di web3/scripts. */
class ChainClient
{
    public function configured(): bool
    {
        return filled(config('chain.rpc_url')) && filled(config('chain.contract')) && filled(config('chain.issuer_key'));
    }

    /**
     * @param  list<string>  $hashes
     * @return array{transaction:?array{tx_hash:string,block_number:int},existing:array<string,?array>,contract:string,issuer:string}
     */
    public function issueBatch(array $hashes, string $classId): array
    {
        return $this->run('anchor', ['hashes' => array_values($hashes), 'classId' => $classId]);
    }

    /** @return array{tx_hash?:string,block_number?:int,already?:bool} */
    public function revoke(string $hash, string $reason): array
    {
        return $this->run('revoke', ['hash' => $hash, 'reason' => $reason]);
    }

    /**
     * Membaca kontrak langsung, ditembolok 60 detik (Spesifikasi Web3, Pembacaan).
     *
     * @return array{exists:bool,issuer:string,issued_at:int,class_id:string,revoked:bool,reason:string}
     */
    public function verify(string $hash): array
    {
        return Cache::remember('chain.verify.'.$hash, 60, fn () => $this->run('verify', ['hash' => $hash], 30));
    }

    public function forget(string $hash): void
    {
        Cache::forget('chain.verify.'.$hash);
    }

    /** @return array{chain_id:int,block:int,issuer:string,balance:string,contract_deployed:bool,issuer_allowed:bool} */
    public function status(): array
    {
        return $this->run('status', [], 30);
    }

    public function recoverSigner(string $message, string $signature): string
    {
        return $this->run('recover', ['message' => $message, 'signature' => $signature], 20)['address'];
    }

    public function explorerTx(?string $hash): ?string
    {
        return $hash && config('chain.explorer_url') ? config('chain.explorer_url').'/tx/'.$hash : null;
    }

    public function explorerAddress(?string $address): ?string
    {
        return $address && config('chain.explorer_url') ? config('chain.explorer_url').'/address/'.$address : null;
    }

    protected function run(string $script, array $input, ?int $timeout = null): array
    {
        if (! $this->configured() && $script !== 'recover') {
            throw new ChainException('Jaringan blockchain belum diatur di server.');
        }

        $result = Process::path(config('chain.scripts'))
            ->timeout($timeout ?? config('chain.timeout'))
            ->env([
                'CHAIN_RPC_URL' => config('chain.rpc_url'),
                'CERT_CONTRACT_ADDRESS' => config('chain.contract'),
                'ISSUER_PRIVATE_KEY' => config('chain.issuer_key'),
            ])
            ->input(json_encode($input))
            ->run([config('chain.node'), $script.'.mjs']);

        if ($result->failed()) {
            throw new ChainException(trim($result->errorOutput()) ?: 'Skrip blockchain gagal tanpa pesan.');
        }

        $data = json_decode($result->output(), true);
        if (! is_array($data)) {
            throw new ChainException('Jawaban skrip blockchain tidak bisa dibaca.');
        }

        return $data;
    }
}
