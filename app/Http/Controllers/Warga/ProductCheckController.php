<?php

namespace App\Http\Controllers\Warga;

use App\Enums\CheckStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Warga\PhotoRequest;
use App\Jobs\CheckProduct;
use App\Models\ProductCheck;
use App\Services\Ai\GeminiClient;
use App\Services\Media\ImageStore;
use App\Services\Products\ProductChecker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProductCheckController extends Controller
{
    public function index(Request $request, GeminiClient $ai): View
    {
        return view('warga.checks.index', [
            'checks' => ProductCheck::query()->where('user_id', $request->user()->id)->latest()->paginate(12),
            'quotaLeft' => $ai->quotaLeft('product_check', $request->user()),
        ]);
    }

    public function store(PhotoRequest $request, ImageStore $images, GeminiClient $ai): RedirectResponse
    {
        if ($ai->quotaLeft('product_check', $request->user()) === 0) {
            return back()->with('error', 'Jatah Cek Produk hari ini sudah habis. Bisa dipakai lagi besok pukul 00.00 WIB.');
        }

        $check = ProductCheck::query()->create([
            'user_id' => $request->user()->id,
            'photo_path' => $images->store($request->file('photo'), 'cek-produk'),
            'status' => CheckStatus::Diperiksa,
        ]);
        CheckProduct::dispatch($check);

        return redirect()->route('warga.checks.show', $check);
    }

    public function show(Request $request, ProductCheck $check, ProductChecker $checker): View
    {
        Gate::authorize('view', $check);

        return view('warga.checks.show', [
            'check' => $check,
            'reference' => $check->status === CheckStatus::Selesai ? $checker->planReference($check) : null,
            'hasShop' => $request->user()->shop()->exists(),
        ]);
    }

    public function update(Request $request, ProductCheck $check): RedirectResponse
    {
        Gate::authorize('update', $check);
        $data = $request->validate(['name' => ['required', 'string', 'max:120']]);

        $check->update(['name' => $data['name'], 'result' => array_merge($check->result ?? [], ['name' => $data['name']])]);

        return back()->with('status', 'Nama produk diperbaiki.');
    }
}
