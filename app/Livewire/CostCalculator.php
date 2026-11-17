<?php

namespace App\Livewire;

use App\Models\BusinessPlan;
use App\Models\CostItem;
use App\Services\Plans\CostCalculator as Calculator;
use Livewire\Attributes\Validate;
use Livewire\Component;

class CostCalculator extends Component
{
    /** @var list<array{name:string,kind:string,amount:int|string}> */
    #[Validate(['rows.*.name' => 'required|string|max:80', 'rows.*.kind' => 'required|in:bahan,tetap', 'rows.*.amount' => 'required|integer|min:0|max:1000000000'])]
    public array $rows = [];

    #[Validate('required|integer|min:1|max:100000')]
    public int|string $units = 50;

    #[Validate('required|integer|min:1|max:100')]
    public int|string $batches = 8;

    #[Validate('required|integer|min:0|max:100000000')]
    public int|string $price = 0;

    public bool $saved = false;

    public function mount(): void
    {
        $plan = $this->plan();
        $this->rows = $plan->costItems()->get(['name', 'kind', 'amount'])->toArray() ?: [
            ['name' => 'Bahan utama', 'kind' => 'bahan', 'amount' => 0],
            ['name' => 'Minyak dan bumbu', 'kind' => 'bahan', 'amount' => 0],
            ['name' => 'Kemasan', 'kind' => 'bahan', 'amount' => 0],
            ['name' => 'Gas dan listrik', 'kind' => 'tetap', 'amount' => 0],
        ];
        $this->units = $plan->batch_units ?: 50;
        $this->batches = $plan->batches_per_month ?: 8;
        $this->price = $plan->selling_price ?? 0;
    }

    public function addRow(string $kind = 'bahan'): void
    {
        $this->rows[] = ['name' => '', 'kind' => $kind, 'amount' => 0];
        $this->saved = false;
    }

    public function removeRow(int $index): void
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
        $this->saved = false;
    }

    public function updated(): void
    {
        $this->saved = false;
    }

    public function save(): void
    {
        $this->validate();
        $plan = $this->plan();
        $plan->update(['batch_units' => (int) $this->units, 'batches_per_month' => (int) $this->batches, 'selling_price' => (int) $this->price]);
        $plan->costItems()->delete();
        foreach ($this->rows as $row) {
            CostItem::query()->create(['business_plan_id' => $plan->id, 'name' => $row['name'], 'kind' => $row['kind'], 'amount' => (int) $row['amount']]);
        }
        $this->saved = true;
    }

    public function render(Calculator $calculator)
    {
        $items = collect($this->rows)->map(fn ($r) => ['kind' => $r['kind'] ?? 'bahan', 'amount' => (int) ($r['amount'] ?? 0)])->all();

        return view('livewire.cost-calculator', [
            'result' => $calculator->calculate($items, (int) $this->units, (int) $this->batches, (int) $this->price),
        ]);
    }

    private function plan(): BusinessPlan
    {
        $user = auth()->user();

        return BusinessPlan::query()->firstOrCreate(['user_id' => $user->id], ['title' => $user->profile?->existing_business ?: 'Usaha saya']);
    }
}
