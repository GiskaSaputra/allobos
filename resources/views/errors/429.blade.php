@extends('errors.layout', ['heading' => 'Terlalu banyak permintaan'])
@section('code', '429')
@section('heading', 'Pelan sedikit')
@section('message', $exception->getMessage() ?: 'Terlalu banyak permintaan dalam waktu singkat. Tunggu satu menit, lalu coba lagi.')
@section('action')<a href="{{ url()->previous() }}" class="btn btn-accent btn-lg">Kembali</a>@endsection
