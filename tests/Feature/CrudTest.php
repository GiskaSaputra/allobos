<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Enums\ShopStatus;
use App\Models\Announcement;
use App\Models\District;
use App\Models\Page;
use App\Models\Shop;
use App\Models\ShopEvent;
use App\Models\Speaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Memastikan ubah data sungguh tersimpan, termasuk foto, di modul yang sering dipakai. */
class CrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_warga_mengubah_lapak_dengan_sampul_baru_lalu_menghapus_produk(): void
    {
        Storage::fake('public');
        $warga = $this->onboardedWarga();
        $shop = Shop::create(['user_id' => $warga->id, 'name' => 'Lapak Lama', 'slug' => 'lapak-lama', 'description' => 'Deskripsi lama.', 'whatsapp' => '6281234567890', 'status' => ShopStatus::Terbit, 'published_at' => now(), 'cover_path' => 'demo/sampul.webp']);
        $product = $shop->products()->create(['name' => 'Keripik', 'price' => 8000, 'unit' => 'bungkus']);

        $this->actingAs($warga)->patch(route('warga.shop.update'), [
            'name' => 'Lapak Baru', 'description' => 'Deskripsi baru yang lebih lengkap.', 'whatsapp' => '081234567890', 'district_id' => District::first()->id,
            'cover' => UploadedFile::fake()->image('sampul.jpg', 2400, 1200)->size(9000),
        ])->assertRedirect(route('warga.shop.edit'))->assertSessionHasNoErrors();

        $shop->refresh();
        $this->assertSame('Lapak Baru', $shop->name);
        $this->assertSame('Deskripsi baru yang lebih lengkap.', $shop->description);
        $this->assertNotSame('demo/sampul.webp', $shop->cover_path);
        Storage::disk('public')->assertExists($shop->cover_path);
        $this->get(route('shops.show', $shop))->assertSee('Lapak Baru')->assertSee($shop->cover_path);

        $this->patch(route('warga.products.update', $product), ['name' => 'Keripik Pedas', 'price' => 9000, 'unit' => 'bungkus', 'photo' => UploadedFile::fake()->image('p.jpg', 1600, 1600)])->assertSessionHasNoErrors();
        $this->assertSame('Keripik Pedas', $product->fresh()->name);
        Storage::disk('public')->assertExists($product->fresh()->photo_path);

        $this->delete(route('warga.products.destroy', $product))->assertSessionHasNoErrors();
        $this->assertSame(0, $shop->products()->count());
    }

    public function test_pengelola_tambah_ubah_hapus_pembicara_dan_pengumuman(): void
    {
        Storage::fake('public');
        $this->actingAs($this->userWithRole(RoleName::Pengelola));

        $this->post(route('pengelola.speakers.store'), ['name' => 'Bu Ani', 'business' => 'Batik Ani', 'photo' => UploadedFile::fake()->image('a.jpg')])->assertSessionHasNoErrors();
        $speaker = Speaker::where('name', 'Bu Ani')->firstOrFail();
        $this->patch(route('pengelola.speakers.update', $speaker), ['name' => 'Bu Ani Lestari', 'business' => 'Batik Ani'])->assertSessionHasNoErrors();
        $this->assertSame('Bu Ani Lestari', $speaker->fresh()->name);
        $this->delete(route('pengelola.speakers.destroy', $speaker))->assertSessionHasNoErrors();
        $this->assertNull(Speaker::find($speaker->id));

        $this->post(route('pengelola.announcements.store'), ['title' => 'Info', 'body' => 'Isi pengumuman.', 'audience' => 'semua'])->assertSessionHasNoErrors();
        $announcement = Announcement::where('title', 'Info')->firstOrFail();
        $this->patch(route('pengelola.announcements.update', $announcement), ['title' => 'Info Baru', 'body' => 'Isi baru.', 'audience' => 'warga'])->assertSessionHasNoErrors();
        $this->assertSame('Info Baru', $announcement->fresh()->title);
        $this->delete(route('pengelola.announcements.destroy', $announcement))->assertSessionHasNoErrors();
        $this->assertNull(Announcement::find($announcement->id));
    }

    public function test_admin_mengubah_bank_kejadian_dan_halaman(): void
    {
        $this->actingAs($this->userWithRole(RoleName::Admin));

        $this->post(route('admin.events.store'), ['day' => 2, 'title' => 'Jalan licin', 'story' => 'Hujan deras membuat jalan licin.', 'demand' => 0.6, 'cost' => 1, 'cash' => 0])->assertSessionHasNoErrors();
        $event = ShopEvent::where('title', 'Jalan licin')->firstOrFail();
        $this->patch(route('admin.events.update', $event), ['day' => 2, 'title' => 'Jalan sangat licin', 'story' => 'Hujan deras sepanjang hari.', 'demand' => 0.5, 'cost' => 1, 'cash' => -5000])->assertSessionHasNoErrors();
        $this->assertSame('Jalan sangat licin', $event->fresh()->title);
        $this->delete(route('admin.events.destroy', $event))->assertSessionHasNoErrors();
        $this->assertNull(ShopEvent::find($event->id));

        $page = Page::firstOrFail();
        $this->patch(route('admin.pages.update', $page), ['title' => 'Judul Baru', 'body' => 'Isi halaman baru.'])->assertSessionHasNoErrors();
        $this->assertSame('Isi halaman baru.', $page->fresh()->body);
        $this->get('/'.$page->slug)->assertSee('Isi halaman baru.');
    }
}
