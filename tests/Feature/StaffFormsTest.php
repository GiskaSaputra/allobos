<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Announcement;
use App\Models\Program;
use App\Models\ShopEvent;
use App\Models\Speaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Formulir tambah di area staf dikirim dengan nilai teks, sama seperti dari peramban. */
class StaffFormsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_menambah_kejadian_dan_mengundang_pengguna(): void
    {
        Mail::fake();
        $this->actingAs($this->userWithRole(RoleName::Admin));

        $this->post(route('admin.events.store'), ['day' => '3', 'kind' => '', 'title' => 'Hujan deras', 'story' => 'Pembeli sepi.', 'demand' => '0.7', 'cost' => '1', 'cash' => '-5000'])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.events.index'));
        $this->assertTrue(ShopEvent::where('title', 'Hujan deras')->exists());

        $this->post(route('admin.users.store'), ['email' => 'calon@contoh.id', 'role' => 'pengelola'])->assertSessionHasNoErrors()->assertRedirect();
    }

    public function test_pengelola_menambah_pembicara_pengumuman_kelas_dan_undangan(): void
    {
        Mail::fake();
        $this->actingAs($this->userWithRole(RoleName::Pengelola));

        $this->post(route('pengelola.speakers.store'), ['name' => 'Bu Ani', 'business' => 'Keripik Ani', 'bio' => 'Pengusaha keripik.', 'user_id' => ''])
            ->assertSessionHasNoErrors()->assertRedirect();
        $speaker = Speaker::where('name', 'Bu Ani')->firstOrFail();

        $this->post(route('pengelola.announcements.store'), ['title' => 'Kelas baru', 'body' => 'Daftar sekarang.', 'audience' => 'semua', 'publish' => '1'])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertTrue(Announcement::where('title', 'Kelas baru')->exists());

        $date = now()->addWeek()->format('Y-m-d');
        $this->post(route('pengelola.programs.store'), [
            'title' => 'Foto Produk Pakai Ponsel', 'kind' => 'workshop', 'business_field_id' => '', 'description' => 'Belajar memotret.',
            'location' => 'Balai Kalurahan', 'quota' => '30', 'price' => '', 'is_official' => '1', 'materials' => '',
            'speakers' => [(string) $speaker->id],
            'sessions' => [['title' => 'Sesi 1', 'date' => $date, 'start' => '09:00', 'end' => '12:00'], ['title' => 'Sesi 2', 'date' => $date, 'start' => '13:00', 'end' => '15:00']],
        ])->assertSessionHasNoErrors()->assertRedirect();
        $program = Program::where('title', 'Foto Produk Pakai Ponsel')->firstOrFail();
        $this->assertSame(2, $program->sessions()->count());

        $this->post(route('pengelola.bosses.invite'), ['email' => 'bos.baru@contoh.id'])->assertSessionHasNoErrors()->assertSessionHas('status');
    }
}
