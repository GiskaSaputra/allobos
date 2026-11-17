<?php

namespace App\Http\Controllers\Public;

use App\Enums\BossStatus;
use App\Http\Controllers\Controller;
use App\Models\BossProfile;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('public.home');
    }

    public function bosses(): View
    {
        return view('public.bosses', [
            'bosses' => BossProfile::query()->with(['user', 'field', 'district'])->where('status', BossStatus::Aktif->value)->orderByDesc('rating_avg')->get(),
        ]);
    }
}
