<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopEvent;
use App\Services\Game\GameRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventBankController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.events.index', [
            'events' => ShopEvent::query()->when($request->query('hari'), fn ($q, $d) => $q->where('day', $d))->orderBy('day')->orderBy('id')->paginate(30)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.events.form', ['event' => new ShopEvent(['day' => 1, 'demand' => 1, 'cost' => 1, 'cash' => 0])]);
    }

    public function store(Request $request): RedirectResponse
    {
        ShopEvent::query()->create($this->data($request));

        return redirect()->route('admin.events.index')->with('status', 'Kejadian ditambahkan ke bank.');
    }

    public function edit(ShopEvent $event): View
    {
        return view('admin.events.form', ['event' => $event]);
    }

    public function update(Request $request, ShopEvent $event): RedirectResponse
    {
        $event->update($this->data($request));

        return redirect()->route('admin.events.index')->with('status', 'Kejadian diperbarui.');
    }

    public function destroy(ShopEvent $event): RedirectResponse
    {
        $event->delete();

        return back()->with('status', 'Kejadian dihapus.');
    }

    private function data(Request $request): array
    {
        $data = $request->validate([
            'day' => ['required', 'integer', 'between:1,7'],
            'kind' => ['nullable', Rule::in(array_keys(GameRules::KINDS))],
            'title' => ['required', 'string', 'max:80'],
            'story' => ['required', 'string', 'max:400'],
            'demand' => ['required', 'numeric', 'between:0.3,2'],
            'cost' => ['required', 'numeric', 'between:0.8,1.5'],
            'cash' => ['required', 'integer', 'between:-50000,50000'],
        ]);

        return $data + ['kind' => null];
    }
}
