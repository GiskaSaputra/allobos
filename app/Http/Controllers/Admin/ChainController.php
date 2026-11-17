<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChainTransaction;
use App\Services\Chain\ChainClient;
use App\Services\Chain\ChainException;
use Illuminate\View\View;

class ChainController extends Controller
{
    public function index(ChainClient $chain): View
    {
        $status = null;
        $error = null;

        if ($chain->configured()) {
            try {
                $status = $chain->status();
            } catch (ChainException $e) {
                $error = $e->getMessage();
            }
        }

        return view('admin.chain', [
            'configured' => $chain->configured(),
            'status' => $status,
            'error' => $error,
            'lowBalance' => $status && (float) $status['balance'] < config('chain.min_balance'),
            'transactions' => ChainTransaction::query()->with('program')->latest()->limit(20)->get(),
            'contractUrl' => $chain->explorerAddress(config('chain.contract')),
            'issuerUrl' => $chain->explorerAddress($status['issuer'] ?? null),
            'chain' => $chain,
        ]);
    }
}
