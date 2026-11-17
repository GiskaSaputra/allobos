<?php

namespace App\Http\Controllers\Warga;

use App\Http\Controllers\Controller;
use App\Models\BusinessPlan;
use App\Models\Opportunity;
use App\Services\Ai\AiException;
use App\Services\Ai\GeminiClient;
use App\Services\Plans\CostCalculator;
use App\Services\Plans\PlanBuilder;
use App\Services\Plans\StepService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class PlanController extends Controller
{
    public function show(Request $request, GeminiClient $ai): View
    {
        $user = $request->user();

        return view('warga.plan', [
            'plan' => $user->plan()->whereNotNull('built_at')->with('opportunity')->first(),
            'chosen' => Opportunity::query()->where('user_id', $user->id)->whereNotNull('chosen_at')->first(),
            'quotaLeft' => $ai->quotaLeft('business_plan', $user),
        ]);
    }

    public function build(Request $request, PlanBuilder $builder): RedirectResponse
    {
        $chosen = Opportunity::query()->where('user_id', $request->user()->id)->whereNotNull('chosen_at')->first();
        if (! $chosen) {
            return redirect()->route('warga.opportunities.index')->with('error', 'Pilih satu peluang atau tulis ide sendiri dulu, baru rencana bisa disusun.');
        }

        try {
            $builder->build($request->user(), $chosen);
        } catch (AiException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('warga.plan.show')->with('status', 'Rencana Usaha dan Langkah 30 Hari sudah disusun. Kamu bisa mengubah tiap bagian.');
    }

    public function update(Request $request): RedirectResponse
    {
        $plan = $request->user()->plan ?? abort(404);
        $section = $request->validate(['section' => ['required', Rule::in([...array_keys(BusinessPlan::SECTIONS), 'title', 'capital', 'risks'])]])['section'];

        $value = $request->validate(['value' => ['required', 'string', 'max:2000']], ['value.required' => 'Bagian ini tidak boleh kosong.'])['value'];

        $plan->update([$section => match ($section) {
            'capital' => $this->parseCapital($value),
            'risks' => array_values(array_filter(array_map('trim', preg_split('/\R/', $value)))),
            'title' => mb_substr(trim($value), 0, 250),
            default => trim($value),
        }]);

        return redirect()->route('warga.plan.show')->with('status', 'Perubahan rencana tersimpan.');
    }

    public function pdf(Request $request, CostCalculator $calculator, StepService $steps): Response
    {
        $user = $request->user();
        $plan = $user->plan()->whereNotNull('built_at')->with('costItems')->first() ?? abort(404);

        return Pdf::loadView('pdf.plan', [
            'plan' => $plan,
            'user' => $user,
            'cost' => $calculator->calculate($plan->costItems->toArray(), $plan->batch_units, $plan->batches_per_month, $plan->selling_price),
            'steps' => $user->steps()->orderBy('week')->orderBy('sort')->get(),
            'progress' => $steps->progress($user),
        ])->download('rencana-usaha.pdf');
    }

    public function cost(): View
    {
        return view('warga.cost');
    }

    /** Baris "Nama - 150000" atau "Nama: Rp150.000" menjadi daftar modal. */
    private function parseCapital(string $value): array
    {
        return collect(preg_split('/\R/', $value))->map(fn ($line) => trim($line))->filter()
            ->map(function ($line) {
                preg_match('/^(.*?)[\s:,-]+(?:rp\.?\s*)?([\d.,]+)\s*$/iu', $line, $m);

                return $m ? ['name' => trim($m[1]), 'amount' => (int) preg_replace('/\D/', '', $m[2])] : ['name' => $line, 'amount' => 0];
            })->values()->all();
    }
}
