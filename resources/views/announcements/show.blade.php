@extends('layouts.app')
@section('title', $announcement->title)
@section('content')
    <div class="bg-white p-6 rounded-xl shadow-sm">
        <h1 class="text-2xl font-bold mb-4">{{ $announcement->title }}</h1>
        <div class="prose max-w-none">{!! $announcement->content !!}</div>
    </div>
@endsection
