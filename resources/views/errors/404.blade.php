@extends('errors.layout', ['heading' => 'Halaman tidak ditemukan'])
@section('code', '404')
@section('heading', 'Halaman tidak ditemukan')
@section('message', 'Alamat ini tidak ada atau sudah dipindah. Periksa lagi alamatnya, atau mulai dari beranda.')
@section('action')<a href="{{ route('home') }}" class="btn btn-accent btn-lg">Ke Beranda</a>@endsection
