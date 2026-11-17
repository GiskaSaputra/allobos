@extends('errors.layout', ['heading' => 'Halaman sudah kedaluwarsa'])
@section('code', '419')
@section('heading', 'Halaman sudah kedaluwarsa')
@section('message', 'Formulir terlalu lama dibiarkan terbuka. Muat ulang halaman, lalu kirim lagi.')
@section('action')<a href="{{ url()->previous() }}" class="btn btn-accent btn-lg">Kembali dan Muat Ulang</a>@endsection
