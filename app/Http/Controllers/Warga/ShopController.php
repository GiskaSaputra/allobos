<?php

namespace App\Http\Controllers\Warga;

use App\Http\Controllers\Controller;
use App\Http\Requests\Warga\ShopRequest;
use App\Models\BusinessField;
use App\Models\District;
use App\Services\Shops\ShopService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class ShopController extends Controller
{
    public function __construct(private readonly ShopService $shops) {}

    public function edit(Request $request): View
    {
        $user = $request->user();
        $shop = $user->shop()->with('products')->first();

        return view('warga.shop.edit', [
            'shop' => $shop,
            'districts' => District::query()->orderBy('name')->pluck('name', 'id'),
            'fields' => BusinessField::query()->usaha()->pluck('name', 'id'),
            'defaults' => ['district_id' => $user->profile?->district_id, 'whatsapp' => $user->whatsapp, 'business_field_id' => $user->profile?->interests[0] ?? null],
            'stats' => $shop ? [
                'visits' => $shop->visits()->where('kind', 'kunjungan')->count(),
                'orders' => $shop->visits()->where('kind', 'pesan')->count(),
            ] : null,
        ]);
    }

    public function store(ShopRequest $request): RedirectResponse
    {
        $this->shops->save($request->user(), $request->safe()->except('cover'), $request->file('cover'));

        return redirect()->route('warga.shop.edit')->with('status', 'Lapak tersimpan. Tambahkan produk, lalu ajukan terbit.');
    }

    public function update(ShopRequest $request): RedirectResponse
    {
        abort_unless($request->user()->shop()->exists(), 404);
        $this->shops->save($request->user(), $request->safe()->except('cover'), $request->file('cover'));

        return redirect()->route('warga.shop.edit')->with('status', 'Data lapak diperbarui.');
    }

    public function submit(Request $request): RedirectResponse
    {
        $shop = $request->user()->shop()->first() ?? abort(404);

        try {
            $this->shops->submit($shop);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Lapak diajukan untuk diperiksa Pengelola IPI.');
    }

    public function toggle(Request $request): RedirectResponse
    {
        $shop = $request->user()->shop()->first() ?? abort(404);

        try {
            $this->shops->toggle($shop);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', $shop->fresh()->status->value === 'tutup' ? 'Lapak ditutup sementara.' : 'Lapak dibuka kembali.');
    }
}
