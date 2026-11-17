<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Certificates\VerificationService;
use App\Services\Chain\ChainClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CertificateVerifyController extends Controller
{
    public function form(): View
    {
        return view('public.verify.form');
    }

    public function lookup(Request $request): RedirectResponse
    {
        $code = $request->validate(['code' => ['required', 'string', 'max:30']], ['code.required' => 'Ketik kode sertifikat dulu, contoh ALB-2026-7KQ4MX.'])['code'];

        return redirect()->route('certificates.verify', strtoupper(preg_replace('/\s+/', '', $code)));
    }

    public function show(string $code, VerificationService $verifier, ChainClient $chain): View
    {
        $result = $verifier->check($code);
        $certificate = $result['certificate'];

        return view('public.verify.show', $result + [
            'code' => strtoupper($code),
            'txUrl' => $chain->explorerTx($certificate?->transaction?->tx_hash),
            'revokeUrl' => $chain->explorerTx($certificate?->revokeTransaction?->tx_hash),
            'contractUrl' => $chain->explorerAddress($certificate?->contract_address),
        ]);
    }
}
