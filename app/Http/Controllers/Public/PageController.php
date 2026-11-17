<?php

namespace App\Http\Controllers\Public;

use App\Enums\BossStatus;
use App\Enums\CertificateStatus;
use App\Enums\ContentStatus;
use App\Enums\ProgramStatus;
use App\Enums\ShopStatus;
use App\Http\Controllers\Controller;
use App\Models\BossProfile;
use App\Models\BusinessField;
use App\Models\Certificate;
use App\Models\Page;
use App\Models\Podcast;
use App\Models\Program;
use App\Models\Shop;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        // AB-15: angka publik dari basis data. Saat mode demo menyala, data contoh tidak ikut dihitung.
        $stats = Cache::remember('beranda.statistik', 600, fn () => [
            'kelas' => Program::query()->when(config('features.demo'), fn ($q) => $q->where('is_demo', false))->where('status', '!=', ProgramStatus::Draf->value)->count(),
            'lapak' => Shop::query()->when(config('features.demo'), fn ($q) => $q->where('is_demo', false))->where('status', ShopStatus::Terbit->value)->count(),
            'bos' => BossProfile::query()->when(config('features.demo'), fn ($q) => $q->where('is_demo', false))->where('status', BossStatus::Aktif->value)->count(),
            'sertifikat' => Certificate::query()->when(config('features.demo'), fn ($q) => $q->where('is_demo', false))->where('status', CertificateStatus::Tercatat->value)->count(),
        ]);

        return view('public.home', [
            'stats' => $stats,
            'fields' => BusinessField::query()->usaha()->pluck('name'),
            'programs' => Program::query()->with(['sessions', 'field'])->whereIn('status', [ProgramStatus::Dibuka->value, ProgramStatus::Penuh->value])->latest()->limit(3)->get(),
            'podcasts' => Podcast::query()->with('field')->where('status', ContentStatus::Terbit->value)->latest('published_at')->limit(3)->get(),
            'shops' => Shop::query()->published()->with(['district', 'field'])->withCount('products')->latest('published_at')->limit(3)->get(),
        ]);
    }

    public function bosses(): View
    {
        return view('public.bosses', [
            'bosses' => BossProfile::query()->with(['user', 'field', 'district'])->where('status', BossStatus::Aktif->value)->orderByDesc('rating_avg')->get(),
        ]);
    }

    public function show(string $slug): View
    {
        $page = Page::query()->where('slug', $slug)->first() ?? abort(404);

        return view('public.page', ['page' => $page]);
    }
}
