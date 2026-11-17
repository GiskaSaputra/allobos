<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CostItem extends Model
{
    public const KINDS = ['bahan' => 'Bahan per produksi', 'tetap' => 'Biaya tetap per bulan'];

    protected $fillable = ['business_plan_id', 'name', 'kind', 'amount'];
}
