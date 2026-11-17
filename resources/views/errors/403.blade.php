@extends('errors.layout', ['heading' => 'Halaman ini bukan untuk peran kamu'])
@section('code', '403')
@section('heading', 'Halaman ini bukan untuk peran kamu')
@section('message', 'Peran yang sedang aktif tidak punya akses ke halaman ini. Kembali ke dashboard peran kamu, atau ganti peran lewat menu akun bila akunmu punya lebih dari satu peran.')
@section('action')<a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="btn btn-accent btn-lg">Ke Dashboard</a>@endsection
