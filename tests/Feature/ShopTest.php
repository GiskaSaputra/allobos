<?php

namespace Tests\Feature;

use App\Enums\CheckStatus;
use App\Enums\RoleName;
use App\Enums\ShopStatus;
use App\Models\District;
use App\Models\ProductCheck;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShopTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_cek_produk_dari_foto_sampai_produk_lapak(): void
    {
        $user = $this->onboardedWarga();
        $this->fakeGemini([
            'name' => 'Keripik singkong balado', 'category' => 'Makanan ringan', 'photo_score' => 3, 'pack_score' => 2,
            'good' => ['Warna cerah'], 'fixes' => ['Pakai latar polos'], 'price_min' => 12000, 'price_max' => 8000,
            'price_reason' => 'Sesuai pasar oleh-oleh', 'signal' => 'naik', 'reasons' => ['Wisatawan ramai'], 'description' => 'Keripik renyah dari singkong Ponjong.',
        ]);

        $this->actingAs($user)->post(route('warga.checks.store'), ['photo' => UploadedFile::fake()->image('keripik.jpg', 2400, 1800)])->assertRedirect();
        $check = ProductCheck::firstOrFail();

        $this->assertSame(CheckStatus::Selesai, $check->status);
        $this->assertSame(8000, $check->result['price_min']);
        $this->assertStringEndsWith('.webp', $check->photo_path);
        [$w] = getimagesizefromstring(Storage::disk('public')->get($check->photo_path));
        $this->assertSame(1280, $w);
        Http::assertSent(fn ($r) => isset($r->data()['contents'][0]['parts'][1]['inlineData']));

        $this->get(route('warga.checks.show', $check))->assertOk()->assertSee('Perkiraan AI')->assertSee('Buat Lapak Dulu');
        $this->patch(route('warga.checks.update', $check), ['name' => 'Keripik pedas'])->assertSessionHas('status');
        $this->assertSame('Keripik pedas', $check->fresh()->name);

        $this->post(route('warga.shop.store'), ['name' => 'Keripik Mbok Sri', 'description' => 'Keripik singkong asli Ponjong.', 'district_id' => District::first()->id, 'whatsapp' => '081234567890'])->assertSessionHas('status');
        $this->get(route('warga.products.create', ['cek' => $check->id]))->assertOk()->assertSee('Keripik pedas')->assertSee('10000');
        $this->post(route('warga.products.store'), ['name' => 'Keripik pedas', 'price' => 10000, 'unit' => 'bungkus', 'product_check_id' => $check->id])->assertSessionHas('status');

        $product = $user->fresh()->shop->products()->first();
        $this->assertNotNull($product->photo_path);
        Storage::disk('public')->assertExists($product->photo_path);
    }

    public function test_cek_produk_tanpa_ai_berstatus_gagal_dengan_penjelasan(): void
    {
        config(['ai.keys' => []]);
        $this->actingAs($this->onboardedWarga())->post(route('warga.checks.store'), ['photo' => UploadedFile::fake()->image('a.png')]);

        $check = ProductCheck::firstOrFail();
        $this->assertSame(CheckStatus::Gagal, $check->status);
        $this->get(route('warga.checks.show', $check))->assertSee('Foto belum bisa diperiksa');
    }

    public function test_berkas_bukan_gambar_ditolak(): void
    {
        $this->actingAs($this->onboardedWarga())
            ->post(route('warga.checks.store'), ['photo' => UploadedFile::fake()->create('virus.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors('photo');
    }

    public function test_lapak_diajukan_disetujui_dikunjungi_dan_dipesan(): void
    {
        $user = $this->onboardedWarga();
        $pengelola = $this->userWithRole(RoleName::Pengelola);
        $shop = $this->shopWithProduct($user);

        $this->actingAs($user)->post(route('warga.shop.submit'))->assertSessionHas('status');
        $this->assertSame(ShopStatus::Menunggu, $shop->fresh()->status);
        $this->assertCount(1, $pengelola->notifications);

        $this->get(route('shops.show', $shop))->assertOk()->assertSee('Pratinjau');
        auth()->logout();
        $this->get(route('shops.show', $shop))->assertNotFound();

        $this->actingAs($pengelola)->post(route('pengelola.shops.approve', $shop))->assertSessionHas('status');
        $this->assertSame(ShopStatus::Terbit, $shop->fresh()->status);
        auth()->logout();

        $this->get(route('shops.index'))->assertSee('Keripik Mbok Sri');
        $this->get(route('shops.show', $shop))->assertOk()->assertSee('Pesan lewat WhatsApp');
        $product = $shop->products()->first();
        $this->post(route('shops.order', [$shop, $product]))->assertRedirectContains('https://wa.me/6281234567890?text=');
        $this->assertSame(1, $shop->visits()->where('kind', 'kunjungan')->count());
        $this->assertSame(1, $shop->visits()->where('kind', 'pesan')->count());

        $product->update(['in_stock' => false]);
        $this->post(route('shops.order', [$shop, $product]))->assertSessionHas('error');

        $this->post(route('shops.report', $shop), ['reason' => 'Foto produk bukan milik penjual'])->assertSessionHas('status');
        $this->actingAs($pengelola)->post(route('pengelola.shops.takedown', $shop), ['reason' => 'Foto milik orang lain'])->assertSessionHas('status');
        $this->assertSame(ShopStatus::Diturunkan, $shop->fresh()->status);
    }

    public function test_lapak_tanpa_produk_berfoto_tidak_bisa_diajukan(): void
    {
        $user = $this->onboardedWarga();
        $shop = Shop::create(['user_id' => $user->id, 'name' => 'Lapak Kosong', 'slug' => 'lapak-kosong', 'description' => 'Belum ada', 'whatsapp' => '6281234567890', 'status' => ShopStatus::Draf]);
        $shop->products()->create(['name' => 'Tanpa foto', 'price' => 1000, 'unit' => 'buah']);

        $this->actingAs($user)->post(route('warga.shop.submit'))->assertSessionHas('error');
        $this->assertSame(ShopStatus::Draf, $shop->fresh()->status);
    }

    public function test_warga_lain_tidak_bisa_mengubah_produk(): void
    {
        $shop = $this->shopWithProduct($this->onboardedWarga());

        $this->actingAs($this->onboardedWarga())
            ->patch(route('warga.products.update', $shop->products()->first()), ['name' => 'X', 'price' => 1000, 'unit' => 'buah'])
            ->assertForbidden();
    }

    private function shopWithProduct($user): Shop
    {
        Storage::disk('public')->put('produk/a.webp', 'x');
        $shop = Shop::create(['user_id' => $user->id, 'name' => 'Keripik Mbok Sri', 'slug' => 'keripik-mbok-sri', 'description' => 'Keripik singkong asli Ponjong.', 'district_id' => District::first()->id, 'whatsapp' => '6281234567890', 'status' => ShopStatus::Draf]);
        $shop->products()->create(['name' => 'Keripik balado', 'price' => 10000, 'unit' => 'bungkus', 'photo_path' => 'produk/a.webp']);

        return $shop;
    }

    public function test_warga_bisa_melihat_lapak_warga_lain_dari_lapak_saya(): void
    {
        $other = $this->onboardedWarga();
        Shop::create(['user_id' => $other->id, 'name' => 'Madu Klanceng Playen', 'slug' => 'madu-klanceng-playen', 'description' => 'Madu.', 'whatsapp' => '6281234567890', 'status' => ShopStatus::Terbit, 'published_at' => now()]);
        Shop::create(['user_id' => $this->onboardedWarga()->id, 'name' => 'Lapak Draf', 'slug' => 'lapak-draf', 'description' => 'Belum terbit.', 'whatsapp' => '6281234567890', 'status' => ShopStatus::Draf]);

        $this->actingAs($this->onboardedWarga())->get(route('warga.shop.edit'))
            ->assertOk()->assertSee('Lapak warga lain')->assertSee('Madu Klanceng Playen')->assertDontSee('Lapak Draf')->assertSee(route('shops.index'));
    }
}
