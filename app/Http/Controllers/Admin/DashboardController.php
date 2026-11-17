<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Models\AiRequest;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = now()->startOfDay();

        return view('admin.dashboard', [
            'roles' => collect(RoleName::cases())->map(fn ($r) => [$r->label(), User::role($r->value)->count()]),
            'ai' => AiRequest::query()->where('created_at', '>=', $today)->select('feature', 'status', DB::raw('count(*) as total'), DB::raw('avg(duration_ms) as avg_ms'))
                ->groupBy('feature', 'status')->get()->groupBy('feature'),
            'failedJobs' => DB::table('failed_jobs')->count(),
            'pendingJobs' => DB::table('jobs')->count(),
            'audit' => AuditLog::query()->with('user')->latest('id')->limit(8)->get(),
            'aiConfigured' => config('ai.keys') !== [],
        ]);
    }
}
