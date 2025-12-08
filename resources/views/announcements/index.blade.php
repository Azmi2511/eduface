@extends('layouts.app')
@section('title','Pengumuman')
@section('content')
    <div class="bg-white p-6 rounded-xl shadow-sm">
        <h3 class="text-lg font-bold mb-4">Daftar Pengumuman</h3>
        <div class="divide-y">
            @forelse($announcements as $a)
                <div class="py-3">
                    <a href="{{ route('announcements.show', $a->id) }}" class="text-blue-600 font-semibold">{{ $a->title }}</a>
                    <p class="text-sm text-gray-500">{{ Str::limit(strip_tags($a->content), 120) }}</p>
                </div>
            @empty
                <div class="py-4 text-gray-500">Tidak ada pengumuman.</div>
            @endforelse
        </div>
    </div>
@endsection
