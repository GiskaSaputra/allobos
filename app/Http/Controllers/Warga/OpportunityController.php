<?php

namespace App\Http\Controllers\Warga;

use App\Http\Controllers\Controller;
use App\Models\Opportunity;
use App\Services\Ai\AiException;
use App\Services\Ai\GeminiClient;
use App\Services\Plans\OpportunityFinder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OpportunityController extends Controller
{
    public function __construct(private readonly OpportunityFinder $finder) {}

    public function index(Request $request, GeminiClient $ai): View
    {
        $user = $request->user();
        $latest = Opportunity::query()->where('user_id', $user->id)->where('is_custom', false)->latest('id')->value('batch');

        return view('warga.opportunities', [
            'items' => $latest ? Opportunity::query()->where('batch', $latest)->orderBy('id')->get() : collect(),
            'chosen' => Opportunity::query()->where('user_id', $user->id)->whereNotNull('chosen_at')->first(),
            'hasPlan' => $user->plan()->whereNotNull('built_at')->exists(),
            'quotaLeft' => $ai->quotaLeft('opportunity', $user),
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        if ($request->filled('title')) {
            $data = $request->validate(['title' => ['required', 'string', 'min:3', 'max:120'], 'why' => ['nullable', 'string', 'max:500']]);
            $this->finder->custom($request->user(), $data['title'], $data['why'] ?? null);

            return redirect()->route('warga.opportunities.index')->with('status', 'Ide usahamu disimpan sebagai Usaha Tujuan. Lanjutkan dengan Susun Rencana.');
        }

        try {
            $this->finder->generate($request->user());
        } catch (AiException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('warga.opportunities.index')->with('status', 'Tiga peluang usaha sudah disusun. Pilih satu yang paling cocok.');
    }

    public function choose(Opportunity $opportunity): RedirectResponse
    {
        Gate::authorize('update', $opportunity);
        $this->finder->choose($opportunity);

        return redirect()->route('warga.opportunities.index')->with('status', '"'.$opportunity->title.'" dipilih sebagai Usaha Tujuan.');
    }
}
