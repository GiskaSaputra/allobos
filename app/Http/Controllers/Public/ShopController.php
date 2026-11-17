<?php

namespace App\Http\Controllers\Public;

use App\Enums\CertificateStatus;
use App\Enums\RoleName;
use App\Enums\ShopStatus;
use App\Http\Controllers\Controller;
use App\Models\BusinessField;
use App\Models\Certificate;
use App\Models\District;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopReport;
use App\Services\Shops\ShopService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(Request $request): View
    {
        $shops = Shop::query()->published()->with(['district', 'field'])->withCount(['products' => fn ($q) => $q->where('hidden', false)])
            ->when($request->query('kapanewon'), fn ($q, $slug) => $q->whereHas('district', fn ($d) => $d->where('slug', $slug)))
            ->when($request->query('bidang'), fn ($q, $slug) => $q->whereHas('field', fn ($f) => $f->where('slug', $slug)))
            ->latest('published_at')->paginate(12)->withQueryString();

        return view('public.shops.index', [
            'shops' => $shops,
            'districts' => District::query()->orderBy('name')->get(['name', 'slug']),
            'fields' => BusinessField::query()->usaha()->get(['name', 'slug']),
        ]);
    }

    public function show(Request $request, Shop $shop): View
    {
        $user = $request->user();
        $canPreview = $user && ($user->id === $shop->user_id || in_array($user->active_role, [RoleName::Pengelola, RoleName::Admin], true));
        abort_unless($shop->status === ShopStatus::Terbit || $canPreview, 404);

        if ($shop->status === ShopStatus::Terbit && $user?->id !== $shop->user_id) {
            $shop->visits()->create(['kind' => 'kunjungan']);
        }

        return view('public.shops.show', [
            'shop' => $shop->load(['district', 'field']),
            'products' => $shop->products()->where('hidden', false)->get(),
            'badges' => Certificate::query()->with('program')->where('user_id', $shop->user_id)
                ->where('status', CertificateStatus::Tercatat->value)->where('show_on_shop', true)->latest('issued_on')->get(),
            'preview' => $shop->status !== ShopStatus::Terbit,
        ]);
    }

    public function order(Shop $shop, Product $product, ShopService $shops): RedirectResponse
    {
        abort_unless($shop->status === ShopStatus::Terbit && $product->shop_id === $shop->id && ! $product->hidden, 404);

        if (! $product->in_stock) {
            return back()->with('error', 'Produk ini sedang habis. Pilih produk lain atau tanya penjual.');
        }

        $shop->visits()->create(['kind' => 'pesan', 'product_id' => $product->id]);

        return redirect()->away($shops->orderUrl($shop, $product));
    }

    public function report(Request $request, Shop $shop): RedirectResponse
    {
        abort_unless($shop->status === ShopStatus::Terbit, 404);
        $data = $request->validate(['reason' => ['required', 'string', 'min:10', 'max:500']], ['reason.min' => 'Ceritakan masalahnya minimal 10 karakter.']);

        ShopReport::query()->create(['shop_id' => $shop->id, 'reason' => $data['reason'], 'ip' => $request->ip()]);

        return back()->with('status', 'Laporan terkirim. Pengelola IPI akan memeriksa lapak ini.');
    }
}
