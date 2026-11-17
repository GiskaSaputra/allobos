<?php

namespace App\Services\Shops;

use App\Enums\RoleName;
use App\Enums\ShopStatus;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Notifications\Pemberitahuan;
use App\Services\Media\ImageStore;
use App\Services\Readiness\ReadinessCalculator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use RuntimeException;

class ShopService
{
    public function __construct(
        private readonly ImageStore $images,
        private readonly ReadinessCalculator $readiness,
    ) {}

    public function save(User $user, array $data, ?UploadedFile $cover = null): Shop
    {
        $shop = $user->shop()->firstOrNew();
        $data['whatsapp'] = User::normalizeWhatsapp($data['whatsapp']);

        if ($cover) {
            $this->images->delete($shop->cover_path);
            $data['cover_path'] = $this->images->store($cover, 'lapak', 'cover', 1600);
        }

        if (! $shop->exists) {
            $data['slug'] = $this->uniqueSlug($data['name']);
            $data['status'] = ShopStatus::Draf;
            $data['is_demo'] = $user->is_demo;
        }

        $shop->fill($data)->save();

        return $shop;
    }

    public function submit(Shop $shop): void
    {
        if (! in_array($shop->status, [ShopStatus::Draf], true)) {
            throw new RuntimeException('Lapak ini sudah diajukan atau sudah terbit.');
        }
        if (! $shop->hasPhotoProduct()) {
            throw new RuntimeException('Tambahkan minimal satu produk berfoto sebelum mengajukan lapak.');
        }
        if (! preg_match('/^628\d{7,12}$/', (string) $shop->whatsapp)) {
            throw new RuntimeException('Nomor WhatsApp lapak belum valid. Perbaiki di data lapak dulu.');
        }

        $shop->update(['status' => ShopStatus::Menunggu, 'reason' => null]);

        Notification::send(
            User::role([RoleName::Pengelola->value])->get(),
            new Pemberitahuan('Lapak menunggu moderasi', 'Lapak '.$shop->name.' diajukan untuk terbit.', route('pengelola.shops.index')),
        );
    }

    public function toggle(Shop $shop): void
    {
        $next = match ($shop->status) {
            ShopStatus::Terbit => ShopStatus::Tutup,
            ShopStatus::Tutup => ShopStatus::Terbit,
            default => throw new RuntimeException('Hanya lapak yang sudah terbit yang bisa ditutup sementara.'),
        };
        $shop->update(['status' => $next]);
        $this->readiness->refresh($shop->user, $next === ShopStatus::Terbit ? 'Lapak dibuka kembali' : 'Lapak ditutup sementara');
    }

    public function approve(Shop $shop): void
    {
        $this->guard($shop, ShopStatus::Menunggu);
        $shop->update(['status' => ShopStatus::Terbit, 'published_at' => $shop->published_at ?? now(), 'reason' => null]);
        AuditLog::record('lapak.setujui', 'shop:'.$shop->id);

        $url = route('shops.show', $shop);
        $shop->user->notify(new Pemberitahuan('Lapak kamu sudah terbit', 'Lapak '.$shop->name.' bisa dibuka pembeli. Bagikan tautannya ke pelanggan.', $url, mail: true));
        $this->readiness->record($shop->user, 'lapak', 'Lapak terbit: '.$shop->name, $url);
    }

    public function reject(Shop $shop, string $reason): void
    {
        $this->guard($shop, ShopStatus::Menunggu);
        $shop->update(['status' => ShopStatus::Draf, 'reason' => $reason]);
        AuditLog::record('lapak.tolak', 'shop:'.$shop->id, ['alasan' => $reason]);

        $shop->user->notify(new Pemberitahuan('Lapak perlu diperbaiki', 'Alasan: '.$reason, route('warga.shop.edit'), mail: true));
    }

    public function takedown(Shop $shop, string $reason): void
    {
        $this->guard($shop, ShopStatus::Terbit);
        $shop->update(['status' => ShopStatus::Diturunkan, 'reason' => $reason]);
        AuditLog::record('lapak.turunkan', 'shop:'.$shop->id, ['alasan' => $reason]);

        $shop->user->notify(new Pemberitahuan('Lapak diturunkan', 'Lapak '.$shop->name.' diturunkan Pengelola IPI. Alasan: '.$reason, route('warga.shop.edit'), mail: true));
        $this->readiness->refresh($shop->user, 'Lapak diturunkan');
    }

    public function orderUrl(Shop $shop, ?Product $product = null): string
    {
        $text = $product
            ? "Halo {$shop->name}, saya mau pesan {$product->name} (Rp".number_format($product->price, 0, ',', '.')." per {$product->unit}). Saya lihat di alloBos."
            : "Halo {$shop->name}, saya mau tanya produk. Saya lihat di alloBos.";

        return 'https://wa.me/'.$shop->whatsapp.'?text='.rawurlencode($text);
    }

    private function guard(Shop $shop, ShopStatus $expected): void
    {
        if ($shop->status !== $expected) {
            throw new RuntimeException('Status lapak sudah berubah menjadi '.$shop->status->label().'. Muat ulang halaman.');
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'lapak';
        $slug = $base;
        for ($i = 2; Shop::query()->where('slug', $slug)->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }
}
