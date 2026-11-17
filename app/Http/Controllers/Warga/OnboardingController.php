<?php

namespace App\Http\Controllers\Warga;

use App\Http\Controllers\Controller;
use App\Http\Requests\Warga\OnboardingStepRequest;
use App\Models\BusinessField;
use App\Models\District;
use App\Models\ResidentProfile;
use App\Services\Readiness\ReadinessCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function show(Request $request, int $step = 1): View|RedirectResponse
    {
        $profile = ResidentProfile::query()->firstOrNew(['user_id' => $request->user()->id], ['step' => 1]);

        abort_unless(isset(ResidentProfile::STEPS[$step]), 404);
        if (! $profile->onboarded_at && $step > $profile->nextStep()) {
            return redirect()->route('warga.onboarding.show', ['step' => $profile->nextStep()]);
        }

        return view('warga.onboarding', [
            'step' => $step,
            'profile' => $profile,
            'districts' => District::query()->orderBy('name')->get(),
            'fields' => BusinessField::query()->usaha()->get(),
        ]);
    }

    public function store(OnboardingStepRequest $request, ReadinessCalculator $readiness, int $step): RedirectResponse
    {
        $user = $request->user();
        $profile = ResidentProfile::query()->firstOrNew(['user_id' => $user->id], ['step' => 1]);

        if (! $profile->onboarded_at && $step > $profile->nextStep()) {
            return redirect()->route('warga.onboarding.show', ['step' => $profile->nextStep()]);
        }

        $data = $request->validated();
        if ($step === 2 && $data['business_status'] === 'belum') {
            $data['existing_business'] = null;
        }
        if ($step === 3) {
            $data['interests'] = array_map('intval', $data['interests']);
        }

        $profile->fill($data);
        $profile->step = max($profile->step, min(5, $step + 1));
        $finished = $step === 5 && ! $profile->onboarded_at;
        if ($finished) {
            $profile->onboarded_at = now();
        }
        $profile->save();

        if ($step < 5) {
            return redirect()->route('warga.onboarding.show', ['step' => $step + 1]);
        }

        $user->setRelation('profile', $profile);
        $readiness->record($user, 'profil', $finished ? 'Melengkapi Profil Usaha' : 'Memperbarui Profil Usaha', route('warga.onboarding.show', ['step' => 1]));

        return redirect()->route('warga.dashboard')->with('status', $finished ? 'Profil Usaha tersimpan. Skor Siap Usaha pertamamu sudah dihitung.' : 'Profil Usaha diperbarui.');
    }
}
