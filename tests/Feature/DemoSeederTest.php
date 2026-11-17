<?php

namespace Tests\Feature;

use App\Enums\CertificateStatus;
use App\Enums\ShopStatus;
use App\Models\ReadinessSnapshot;
use App\Models\User;
use App\Services\Chain\ChainClient;
use App\Services\Readiness\ReadinessCalculator;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Fakes\FakeChainClient;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_skenario_bu_sri_dihitung_dengan_rumus_produksi(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $this->app->instance(ChainClient::class, new FakeChainClient);

        $this->seed(DemoSeeder::class);

        $sri = User::where('email', 'warga@allobos.test')->firstOrFail();
        $snapshots = ReadinessSnapshot::where('user_id', $sri->id)->orderBy('created_at')->orderBy('id')->pluck('score');

        $this->assertSame(10, $snapshots->first(), 'Skor awal setelah Profil Usaha');
        $this->assertSame(app(ReadinessCalculator::class)->score(app(ReadinessCalculator::class)->breakdown($sri)), $snapshots->last());
        $this->assertGreaterThanOrEqual(80, $snapshots->last());
        $this->assertTrue($snapshots->sliding(2)->every(fn ($pair) => $pair->last() >= $pair->first()), 'Skor naik di setiap langkah');

        $certificate = $sri->certificates()->firstOrFail();
        $this->assertSame(CertificateStatus::Tercatat, $certificate->status);
        $this->assertTrue($certificate->is_demo);
        $this->assertSame(ShopStatus::Terbit, $sri->shop->status);

        foreach (['warga', 'bos', 'pengelola', 'admin'] as $role) {
            $this->post('/masuk', ['email' => $role.'@allobos.test', 'password' => env('DEMO_PASSWORD', 'allobos-demo')]);
            $this->get(route('dashboard'))->assertRedirect();
            $this->followRedirects($this->get(route('dashboard')))->assertOk();
            $this->post('/keluar');
        }

        $this->get('/')->assertOk()->assertSee('Keripik Mbok Sri');
        $this->get(route('certificates.verify', $certificate->code))->assertSee('Asli');
    }
}
