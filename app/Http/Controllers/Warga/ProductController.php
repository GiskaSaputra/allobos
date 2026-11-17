<?php

namespace App\Http\Controllers\Warga;

use App\Enums\CheckStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Warga\ProductRequest;
use App\Models\Product;
use App\Models\ProductCheck;
use App\Services\Media\ImageStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly ImageStore $images) {}

    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->user()->shop()->exists()) {
            return redirect()->route('warga.shop.edit')->with('error', 'Buat lapak dulu, baru tambah produk.');
        }

        $check = $request->filled('cek')
            ? ProductCheck::query()->where('user_id', $request->user()->id)->where('status', CheckStatus::Selesai->value)->find($request->integer('cek'))
            : null;

        return view('warga.shop.product', ['product' => new Product(['unit' => 'bungkus']), 'check' => $check, 'checks' => $this->checks($request)]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $shop = $request->user()->shop()->first() ?? abort(404);
        $data = $request->safe()->except(['photo', 'product_check_id']);
        $data['hidden'] = $request->boolean('hidden');
        $data['photo_path'] = $this->photo($request, null);
        $data['product_check_id'] = $this->ownedCheck($request)?->id;
        $data['sort'] ??= (int) $shop->products()->max('sort') + 1;

        $shop->products()->create($data);

        return redirect()->route('warga.shop.edit')->with('status', 'Produk ditambahkan ke lapak.');
    }

    public function edit(Request $request, Product $product): View
    {
        Gate::authorize('update', $product);

        return view('warga.shop.product', ['product' => $product, 'check' => null, 'checks' => $this->checks($request)]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        Gate::authorize('update', $product);

        if ($request->has('in_stock')) {
            $product->update(['in_stock' => $request->boolean('in_stock')]);

            return back()->with('status', $product->in_stock ? 'Produk ditandai tersedia.' : 'Produk ditandai habis. Tombol pesan untuk produk ini dimatikan.');
        }

        $data = $request->safe()->except(['photo', 'product_check_id']);
        $data['hidden'] = $request->boolean('hidden');
        if ($photo = $this->photo($request, $product->photo_path)) {
            $data['photo_path'] = $photo;
        }
        $product->update($data);

        return redirect()->route('warga.shop.edit')->with('status', 'Produk diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        Gate::authorize('delete', $product);
        if (! $product->product_check_id) {
            $this->images->delete($product->photo_path);
        }
        $product->delete();

        return redirect()->route('warga.shop.edit')->with('status', 'Produk dihapus.');
    }

    /** Foto baru diunggah, atau disalin dari foto Cek Produk milik warga. */
    private function photo(ProductRequest $request, ?string $current): ?string
    {
        if ($request->hasFile('photo')) {
            $this->images->delete($current);

            return $this->images->store($request->file('photo'), 'produk');
        }

        if ($check = $this->ownedCheck($request)) {
            $copy = 'produk/'.Str::uuid().'.webp';
            Storage::disk('public')->copy($check->photo_path, $copy);

            return $copy;
        }

        return null;
    }

    private function ownedCheck(Request $request): ?ProductCheck
    {
        return $request->filled('product_check_id')
            ? ProductCheck::query()->where('user_id', $request->user()->id)->find($request->integer('product_check_id'))
            : null;
    }

    private function checks(Request $request)
    {
        return ProductCheck::query()->where('user_id', $request->user()->id)->where('status', CheckStatus::Selesai->value)->latest()->limit(10)->get();
    }
}
