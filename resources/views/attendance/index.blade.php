@extends('layouts.app')
@section('title','Absensi')
@section('content')
<div class="bg-white p-6 rounded-xl shadow-sm">
    <h3 class="text-lg font-bold mb-4">Absensi - {{ $date }}</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50 text-xs text-gray-600 uppercase font-semibold">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Siswa</th>
                    <th class="px-4 py-2">Waktu</th>
                    <th class="px-4 py-2">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $l)
                    <tr class="border-t">
                        <td class="px-4 py-3">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3">{{ $l->student ? $l->student->full_name : $l->student_nisn }}</td>
                        <td class="px-4 py-3">{{ date('H:i', strtotime($l->time_log)) }}</td>
                        <td class="px-4 py-3">{{ $l->status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
