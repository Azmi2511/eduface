@extends('layouts.app')
@section('title','Siswa')
@section('content')
<div class="bg-white p-6 rounded-xl shadow-sm">
    <h3 class="text-lg font-bold mb-4">Daftar Siswa</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50 text-xs text-gray-600 uppercase font-semibold">
                <tr>
                    <th class="px-4 py-2">NISN</th>
                    <th class="px-4 py-2">Nama</th>
                    <th class="px-4 py-2">Kelas</th>
                    <th class="px-4 py-2">Gender</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $s)
                    <tr class="border-t">
                        <td class="px-4 py-3">{{ $s->nisn }}</td>
                        <td class="px-4 py-3">{{ $s->full_name }}</td>
                        <td class="px-4 py-3">{{ $s->class ? $s->class->class_name : '-' }}</td>
                        <td class="px-4 py-3">{{ $s->gender }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
