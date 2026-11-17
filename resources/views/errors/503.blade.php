@extends('errors.layout', ['heading' => 'Sedang dirawat'])
@section('code', '503')
@section('heading', 'alloBos sedang dirawat')
@section('message', 'Kami sedang memperbarui sistem. Coba buka lagi sebentar lagi.')
@section('action')<a href="{{ url('/') }}" class="btn btn-accent btn-lg">Muat Ulang</a>@endsection
