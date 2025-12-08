@extends('layouts.app')
@section('title','Kelas')
@section('content')
<div class="bg-white p-6 rounded-xl shadow-sm">
    <h3 class="text-lg font-bold mb-4">Daftar Kelas</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50 text-xs text-gray-600 uppercase font-semibold">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Nama Kelas</th>
                    <th class="px-4 py-2">Tingkat</th>
                    <th class="px-4 py-2">Tahun Ajar</th>
                </tr>
            </thead>
            <tbody>
                @foreach($classes as $c)
                    <tr class="border-t">
                        <td class="px-4 py-3">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3">{{ $c->class_name }}</td>
                        <td class="px-4 py-3">{{ $c->grade_level }}</td>
                        <td class="px-4 py-3">{{ $c->academic_year }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
