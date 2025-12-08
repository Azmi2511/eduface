@extends('layouts.app')
@section('title','Pengguna')
@section('content')
<div class="bg-white p-6 rounded-xl shadow-sm">
    <h3 class="text-lg font-bold mb-4">Daftar Pengguna</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50 text-xs text-gray-600 uppercase font-semibold">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Username</th>
                    <th class="px-4 py-2">Role</th>
                    <th class="px-4 py-2">Email</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                    <tr class="border-t">
                        <td class="px-4 py-3">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3">{{ $u->username }}</td>
                        <td class="px-4 py-3">{{ $u->role }}</td>
                        <td class="px-4 py-3">{{ $u->email }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
