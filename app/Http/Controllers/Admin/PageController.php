<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        return view('admin.pages.index', ['pages' => Page::query()->orderBy('slug')->get()]);
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.edit', ['page' => $page]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:120'], 'body' => ['required', 'string', 'max:30000']]);
        $page->update($data + ['updated_by' => $request->user()->id]);
        AuditLog::record('halaman.ubah', 'page:'.$page->slug);

        return redirect()->route('admin.pages.index')->with('status', 'Halaman '.$page->title.' diperbarui.');
    }
}
