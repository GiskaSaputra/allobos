<?php

namespace Tests\Feature;

use App\Enums\StepOrigin;
use App\Livewire\CostCalculator;
use App\Models\Opportunity;
use App\Models\PlanStep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class PlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_peluang_ai_dipilih_lalu_rencana_dan_langkah_disusun(): void
    {
        $user = $this->onboardedWarga();
        $item = fn ($t) => ['title' => $t, 'why' => 'Cocok dengan modal kecilmu di Ponjong.', 'capital_range' => 'Rp500 ribu sampai Rp1 juta', 'risk' => 'Harga singkong naik', 'ipi_field' => 'kuliner-dan-makanan-olahan'];
        $this->fakeGemini(
            ['items' => [$item('Keripik singkong pedas'), $item('Tiwul instan'), $item('Gethuk kemasan')]],
            ['product' => 'Keripik', 'customers' => 'Wisatawan', 'channels' => 'Warung', 'rivals' => 'Pabrik', 'capital' => [['name' => 'Wajan', 'amount' => 200000]], 'risks' => ['Harga naik'], 'steps' => [['week' => 1, 'title' => 'Beli bahan'], ['week' => 2, 'title' => 'Uji rasa'], ['week' => 3, 'title' => 'Titip warung'], ['week' => 4, 'title' => 'Buka lapak']]],
        );

        $this->actingAs($user)->post(route('warga.opportunities.generate'))->assertRedirect(route('warga.opportunities.index'));
        $this->assertSame(3, Opportunity::where('user_id', $user->id)->where('is_ai', true)->count());
        $this->get(route('warga.opportunities.index'))->assertSee('Keripik singkong pedas')->assertSee('Perkiraan AI');

        $chosen = Opportunity::where('title', 'Tiwul instan')->first();
        $this->post(route('warga.opportunities.choose', $chosen))->assertSessionHas('status');
        $this->post(route('warga.plan.build'))->assertRedirect(route('warga.plan.show'));

        $plan = $user->fresh()->plan;
        $this->assertSame('Tiwul instan', $plan->title);
        $this->assertSame(4, $user->steps()->where('origin', StepOrigin::Rencana->value)->count());

        $this->patch(route('warga.plan.update'), ['section' => 'capital', 'value' => "Wajan - 250000\nKemasan: Rp75.000"])->assertRedirect();
        $this->assertSame(325000, $plan->fresh()->capitalTotal());

        $this->get(route('warga.plan.pdf'))->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_peluang_tanpa_ai_memakai_kata_kunci_dan_menyebut_data_warga(): void
    {
        $user = $this->onboardedWarga();
        config(['ai.keys' => []]);
        Http::fake();

        $this->actingAs($user)->post(route('warga.opportunities.generate'));
        $items = Opportunity::where('user_id', $user->id)->get();

        $this->assertCount(3, $items);
        $this->assertFalse($items->first()->is_ai);
        $this->assertSame('kuliner-dan-makanan-olahan', $items->first()->ipi_field);
        $this->assertStringContainsString('Ponjong', $items->first()->why);
    }

    public function test_ide_sendiri_dan_rencana_tanpa_ai(): void
    {
        $user = $this->onboardedWarga();
        config(['ai.keys' => []]);

        $this->actingAs($user)->post(route('warga.opportunities.generate'), ['title' => 'Warung bakso', 'why' => 'Saya suka masak'])->assertSessionHas('status');
        $this->post(route('warga.plan.build'));

        $this->assertFalse($user->fresh()->plan->is_ai);
        $this->assertSame(8, $user->steps()->count());
    }

    public function test_langkah_dicentang_menaikkan_persentase_dan_milik_sendiri(): void
    {
        $user = $this->onboardedWarga();
        $step = PlanStep::create(['user_id' => $user->id, 'week' => 1, 'title' => 'Beli bahan', 'origin' => StepOrigin::Sendiri]);
        PlanStep::create(['user_id' => $user->id, 'week' => 2, 'title' => 'Uji rasa', 'origin' => StepOrigin::Sendiri]);

        $this->actingAs($user)->patch(route('warga.steps.update', $step), ['done' => 1]);
        $this->assertNotNull($step->fresh()->done_at);
        $this->get(route('warga.steps.index'))->assertSee('50%');

        $this->post(route('warga.steps.store'), ['title' => 'Foto produk', 'week' => 3])->assertSessionHas('status');
        $this->assertSame(3, $user->steps()->count());

        $this->actingAs($this->onboardedWarga())->patch(route('warga.steps.update', $step), ['done' => 0])->assertForbidden();
    }

    public function test_kalkulator_hpp_livewire_menghitung_dan_menyimpan(): void
    {
        $user = $this->onboardedWarga();
        $this->actingAs($user);

        Livewire::test(CostCalculator::class)
            ->set('rows', [['name' => 'Singkong', 'kind' => 'bahan', 'amount' => 100000], ['name' => 'Gas', 'kind' => 'tetap', 'amount' => 80000]])
            ->set('units', 50)->set('batches', 8)->set('price', 5000)
            ->assertSee('Rp2.200')
            ->assertSee('Jual minimal 27 unit')
            ->call('save')
            ->assertSet('saved', true);

        $this->assertSame(5000, $user->fresh()->plan->selling_price);
        $this->assertSame(2, $user->fresh()->plan->costItems()->count());
        $this->get(route('warga.plan.cost'))->assertOk()->assertSeeLivewire(CostCalculator::class);
    }
}
