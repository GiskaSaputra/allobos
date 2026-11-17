<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemController extends Controller
{
    public function audit(Request $request): View
    {
        return view('admin.audit', [
            'logs' => AuditLog::query()->with('user')
                ->when($request->query('aksi'), fn ($q, $a) => $q->where('action', 'like', $a.'%'))
                ->latest('id')->paginate(40)->withQueryString(),
            'actions' => AuditLog::query()->distinct()->orderBy('action')->pluck('action')->map(fn ($a) => explode('.', $a)[0])->unique()->values(),
        ]);
    }
}
