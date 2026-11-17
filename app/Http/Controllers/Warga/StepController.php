<?php

namespace App\Http\Controllers\Warga;

use App\Enums\StepOrigin;
use App\Http\Controllers\Controller;
use App\Models\PlanStep;
use App\Services\Plans\StepService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class StepController extends Controller
{
    public function __construct(private readonly StepService $steps) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('warga.steps', [
            'weeks' => $user->steps()->orderBy('week')->orderBy('sort')->get()->groupBy('week'),
            'progress' => $this->steps->progress($user),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:250'], 'week' => ['required', 'integer', 'between:1,4']]);
        $this->steps->addMany($request->user(), [$data['title']], StepOrigin::Sendiri, (int) $data['week']);

        return redirect()->route('warga.steps.index')->with('status', 'Langkah ditambahkan.');
    }

    public function update(Request $request, PlanStep $step): RedirectResponse
    {
        Gate::authorize('update', $step);

        if ($request->has('done')) {
            $this->steps->toggle($step, $request->boolean('done'));

            return redirect()->to(route('warga.steps.index').'#langkah-'.$step->id);
        }

        $data = $request->validate(['title' => ['required', 'string', 'max:250'], 'week' => ['required', 'integer', 'between:1,4']]);
        $step->update($data);

        return redirect()->route('warga.steps.index')->with('status', 'Langkah diubah.');
    }

    public function destroy(PlanStep $step): RedirectResponse
    {
        Gate::authorize('delete', $step);
        $this->steps->remove($step);

        return redirect()->route('warga.steps.index')->with('status', 'Langkah dihapus.');
    }
}
