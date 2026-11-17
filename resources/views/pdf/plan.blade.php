@extends('pdf.layout', ['title' => 'Rencana Usaha'])
@php($rp = fn ($n) => 'Rp'.number_format((float) $n, 0, ',', '.'))
@section('content')
    <h1>Rencana Usaha: {{ $plan->title }}</h1>
    <p class="muted">{{ $user->name }}. Disusun {{ $plan->built_at->translatedFormat('d F Y') }}{{ $plan->is_ai ? ', dibantu AI (perkiraan)' : '' }}.</p>
    @foreach (App\Models\BusinessPlan::SECTIONS as $key => $label)
        <h2>{{ $label }}</h2>
        <div class="box">{{ $plan->$key }}</div>
    @endforeach
    <h2>Kebutuhan modal</h2>
    <table>
        @foreach ($plan->capital ?? [] as $item)<tr><td>{{ $item['name'] }}</td><td style="text-align:right">{{ $rp($item['amount']) }}</td></tr>@endforeach
        <tr><th>Total</th><th style="text-align:right">{{ $rp($plan->capitalTotal()) }}</th></tr>
    </table>
    <h2>Risiko</h2>
    <ul>@foreach ($plan->risks ?? [] as $risk)<li>{{ $risk }}</li>@endforeach</ul>
    @if ($cost['valid'] && $plan->selling_price > 0)
        <h2>HPP dan titik impas</h2>
        <table>
            <tr><td>HPP per unit</td><td style="text-align:right">{{ $rp($cost['hpp_unit']) }}</td></tr>
            <tr><td>Harga jual</td><td style="text-align:right">{{ $rp($plan->selling_price) }}</td></tr>
            <tr><td>Untung per unit</td><td style="text-align:right">{{ $rp($cost['margin']) }}</td></tr>
            <tr><td>Titik impas per bulan</td><td style="text-align:right">{{ $cost['break_even_units'] !== null ? number_format($cost['break_even_units'], 0, ',', '.').' unit' : 'Harga di bawah biaya bahan' }}</td></tr>
        </table>
    @endif
    <h2>Langkah 30 Hari ({{ $progress['percent'] }}% selesai)</h2>
    <table>
        @foreach ($steps as $step)<tr><td style="width:70px">Minggu {{ $step->week }}</td><td>{{ $step->title }}</td><td style="width:60px">{{ $step->done_at ? 'Selesai' : '' }}</td></tr>@endforeach
    </table>
@endsection
