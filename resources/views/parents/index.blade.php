@extends('layouts.app')
@section('title','Orang Tua')
@section('content')
<div class="bg-white p-6 rounded-xl shadow-sm">
    <h3 class="text-lg font-bold mb-4">Daftar Orang Tua</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50 text-xs text-gray-600 uppercase font-semibold">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Nama</th>
                    <th class="px-4 py-2">Telepon</th>
                </tr>
            </thead>
            <tbody>
                @foreach($parents as $p)
                    <tr class="border-t">
                        <td class="px-4 py-3">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3">{{ $p->full_name }}</td>
                        <td class="px-4 py-3">{{ $p->phone_number }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
