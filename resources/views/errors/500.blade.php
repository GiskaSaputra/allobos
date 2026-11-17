@extends('errors.layout', ['heading' => 'Ada kesalahan di server'])
@section('code', '500')
@section('heading', 'Ada kesalahan di server')
@section('message', 'Sistem kami sedang bermasalah dan sudah mencatat kejadian ini. Coba lagi beberapa menit lagi.')
@section('action')<a href="{{ route('home') }}" class="btn btn-accent btn-lg">Ke Beranda</a>@endsection
