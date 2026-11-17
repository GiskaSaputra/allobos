<?php

namespace App\Http\Controllers\Pengelola;

use App\Enums\ShopStatus;
use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\ShopReport;
use App\Services\Shops\ShopService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class ShopModerationController extends Controller
{
    public function __construct(private readonly ShopService $shops) {}

    public function index(Request $request): View
    {
        $request->validate(['status' => ['nullable', Rule::enum(ShopStatus::class)]]);
        $status = $request->query('status', ShopStatus::Menunggu->value);

        return view('pengelola.shops.index', [
            'status' => $status,
            'shops' => Shop::query()->with(['user', 'district'])->withCount('products')
                ->where('status', $status)
                ->when($request->query('q'), fn ($q, $term) => $q->where('name', 'like', '%'.$term.'%'))
                ->latest('updated_at')->paginate(20)->withQueryString(),
            'counts' => Shop::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'reports' => ShopReport::query()->whereNull('handled_at')->selectRaw('shop_id, count(*) as total')->groupBy('shop_id')->pluck('total', 'shop_id'),
        ]);
    }

    public function approve(Shop $shop): RedirectResponse
    {
        return $this->act(fn () => $this->shops->approve($shop), 'Lapak '.$shop->name.' diterbitkan.');
    }

    public function reject(Request $request, Shop $shop): RedirectResponse
    {
        $reason = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:300']])['reason'];

        return $this->act(fn () => $this->shops->reject($shop, $reason), 'Lapak '.$shop->name.' dikembalikan ke pemilik untuk diperbaiki.');
    }

    public function takedown(Request $request, Shop $shop): RedirectResponse
    {
        $reason = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:300']])['reason'];

        return $this->act(function () use ($shop, $reason) {
            $this->shops->takedown($shop, $reason);
            $shop->reports()->whereNull('handled_at')->update(['handled_at' => now()]);
        }, 'Lapak '.$shop->name.' diturunkan.');
    }

    private function act(callable $action, string $message): RedirectResponse
    {
        try {
            $action();
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', $message);
    }
}
