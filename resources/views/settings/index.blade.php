@extends('layouts.app')
@section('title','Pengaturan')
@section('content')
<div class="bg-white p-6 rounded-xl shadow-sm">
    <h3 class="text-lg font-bold mb-4">Pengaturan Sistem</h3>
    <div class="space-y-2 text-sm text-gray-700">
        <p>Atur nama sekolah, jam masuk, batas keterlambatan, notifikasi, dll.</p>
        <p>Gunakan model `SystemSetting` untuk menyimpan dan membaca.</p>
    </div>
</div>
@endsection
