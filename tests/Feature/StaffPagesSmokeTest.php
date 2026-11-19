<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\BossProfile;
use App\Models\BossSlot;
use App\Models\Certificate;
use App\Models\MentoringSession;
use App\Models\Page;
use App\Models\Podcast;
use App\Models\Program;
use App\Models\Shop;
use App\Models\ShopEvent;
use App\Models\Speaker;
use App\Models\User;
use App\Services\Chain\ChainClient;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\Fakes\FakeChainClient;
use Tests\TestCase;

/** Setiap halaman GET di area staf terbuka tanpa galat server untuk akun demo yang berhak. */
class StaffPagesSmokeTest extends TestCase
{
    use RefreshDatabase;

    private const AREAS = [
        'admin@allobos.com' => ['admin/', 'pengelola/', 'tanda-tangan'],
        'pengelola@allobos.com' => ['pengelola/', 'tanda-tangan'],
        'pimpinan@allobos.com' => ['pengelola/', 'tanda-tangan'],
        'bos@allobos.com' => ['bos-area', 'tanda-tangan'],
    ];

    public function test_semua_halaman_staf_terbuka(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $this->app->instance(ChainClient::class, new FakeChainClient);
        $this->seed(DemoSeeder::class);

        $failures = [];
        foreach (self::AREAS as $email => $prefixes) {
            $this->actingAs(User::where('email', $email)->firstOrFail());
            foreach (Route::getRoutes() as $route) {
                if (! in_array('GET', $route->methods()) || ! collect($prefixes)->contains(fn ($p) => str_starts_with($route->uri(), $p))) {
                    continue;
                }
                $params = $this->params($route->parameterNames());
                if ($params === null) {
                    continue;
                }
                $url = route($route->getName(), $params);
                $status = $this->get($url)->getStatusCode();
                if ($status >= 500) {
                    $failures[] = "$email $url $status";
                }
            }
        }

        $this->assertSame([], $failures);
    }

    /** Parameter rute diisi baris pertama modelnya. Null bila datanya tidak ada. */
    private function params(array $names): ?array
    {
        $models = [
            'program' => Program::class, 'speaker' => Speaker::class, 'podcast' => Podcast::class,
            'announcement' => Announcement::class, 'user' => User::class, 'page' => Page::class,
            'event' => ShopEvent::class, 'session' => MentoringSession::class, 'certificate' => Certificate::class,
            'boss' => BossProfile::class, 'shop' => Shop::class, 'slot' => BossSlot::class,
        ];
        $out = [];
        foreach ($names as $name) {
            if (! isset($models[$name]) || ! class_exists($models[$name])) {
                return null;
            }
            $id = $models[$name]::query()->value('id');
            if ($id === null) {
                return null;
            }
            $out[$name] = $id;
        }

        return $out;
    }
}
