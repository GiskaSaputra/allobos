<?php

namespace App\Http\Controllers\Pengelola;

use App\Enums\BossStatus;
use App\Enums\CertificateStatus;
use App\Enums\ContentStatus;
use App\Enums\ProgramStatus;
use App\Enums\ShopStatus;
use App\Http\Controllers\Controller;
use App\Models\BossProfile;
use App\Models\Certificate;
use App\Models\ChainTransaction;
use App\Models\Podcast;
use App\Models\Program;
use App\Models\ResidentProfile;
use App\Models\Shop;
use App\Models\ShopReport;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('pengelola.dashboard', [
            'stats' => [
                ['Warga dengan profil lengkap', ResidentProfile::query()->whereNotNull('onboarded_at')->count(), null],
                ['Kelas dibuka atau berjalan', Program::query()->whereIn('status', [ProgramStatus::Dibuka->value, ProgramStatus::Penuh->value, ProgramStatus::Berjalan->value])->count(), 'pengelola.programs.index'],
                ['Lapak terbit', Shop::query()->where('status', ShopStatus::Terbit->value)->count(), 'shops.index'],
                ['Bos aktif', BossProfile::query()->where('status', BossStatus::Aktif->value)->count(), 'bosses.index'],
            ],
            'todo' => array_filter([
                ['Lapak menunggu moderasi', Shop::query()->where('status', ShopStatus::Menunggu->value)->count(), route('pengelola.shops.index')],
                ['Laporan lapak belum ditangani', ShopReport::query()->whereNull('handled_at')->count(), route('pengelola.shops.index', ['status' => 'terbit'])],
                ['Profil Bos menunggu kurasi', BossProfile::query()->where('status', BossStatus::Diajukan->value)->count(), route('pengelola.bosses.index')],
                ['Episode siniar draf', Podcast::query()->where('status', ContentStatus::Draf->value)->count(), route('pengelola.podcasts.index')],
                ['Sertifikat menunggu tanda tangan', Certificate::query()->where('status', CertificateStatus::MenungguTandaTangan->value)->count(), route('pengelola.certificates.index', ['status' => 'menunggu_tanda_tangan'])],
                ['Pencatatan blockchain gagal', ChainTransaction::query()->where('status', 'gagal')->count(), route('pengelola.certificates.index')],
            ], fn ($row) => $row[1] > 0),
            'programs' => Program::query()->with('sessions')->whereIn('status', [ProgramStatus::Dibuka->value, ProgramStatus::Penuh->value, ProgramStatus::Berjalan->value])->latest()->limit(5)->get(),
        ]);
    }
}
