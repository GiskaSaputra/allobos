<?php

namespace Tests\Fakes;

use App\Services\Chain\ChainClient;
use App\Services\Chain\ChainException;

class FakeChainClient extends ChainClient
{
    /** @var array<string,array> */
    public array $records = [];

    public bool $down = false;

    public int $block = 100;

    public function configured(): bool
    {
        return true;
    }

    public function issueBatch(array $hashes, string $classId): array
    {
        $this->fail();
        foreach ($hashes as $hash) {
            $this->records[$hash] = ['exists' => true, 'issuer' => '0x'.str_repeat('a', 40), 'issued_at' => time(), 'class_id' => $classId, 'revoked' => false, 'reason' => ''];
        }

        return ['transaction' => ['tx_hash' => '0x'.str_repeat('b', 64), 'block_number' => ++$this->block], 'existing' => [], 'contract' => '0x'.str_repeat('c', 40), 'issuer' => '0x'.str_repeat('a', 40)];
    }

    public function revoke(string $hash, string $reason): array
    {
        $this->fail();
        $this->records[$hash]['revoked'] = true;
        $this->records[$hash]['reason'] = $reason;

        return ['tx_hash' => '0x'.str_repeat('d', 64), 'block_number' => ++$this->block];
    }

    public function verify(string $hash): array
    {
        $this->fail();

        return $this->records[$hash] ?? ['exists' => false, 'issuer' => '0x'.str_repeat('0', 40), 'issued_at' => 0, 'class_id' => '0x', 'revoked' => false, 'reason' => ''];
    }

    public function status(): array
    {
        $this->fail();

        return ['chain_id' => 31337, 'block' => $this->block, 'issuer' => '0x'.str_repeat('a', 40), 'balance' => '0.01', 'contract_deployed' => true, 'issuer_allowed' => true];
    }

    private function fail(): void
    {
        if ($this->down) {
            throw new ChainException('RPC tidak tersedia');
        }
    }
}
