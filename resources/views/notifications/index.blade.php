@extends('layouts.app')
@section('title','Notifikasi')
@section('content')
<div class="bg-white p-6 rounded-xl shadow-sm">
    <h3 class="text-lg font-bold mb-4">Notifikasi</h3>
    <div class="divide-y">
        @foreach($notifications as $n)
            <div class="py-3">
                <p class="text-sm">{{ $n->message }}</p>
                <p class="text-xs text-gray-400">{{ $n->created_at }}</p>
            </div>
        @endforeach
    </div>
</div>
@endsection
