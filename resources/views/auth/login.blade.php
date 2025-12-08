@extends('layouts.app')

@section('title','Login')

@section('content')
<div class="max-w-md mx-auto mt-20 bg-white p-8 rounded-xl shadow-md">
    <h3 class="text-xl font-bold mb-6">Login</h3>
    @if($errors->any())
        <div class="text-red-600 mb-4">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('login.perform') }}">
        @csrf
        <div class="mb-4">
            <label class="block text-sm mb-1">Username</label>
            <input name="username" type="text" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-4">
            <label class="block text-sm mb-1">Password</label>
            <input name="password" type="password" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
            <button class="px-4 py-2 bg-blue-600 text-white rounded">Login</button>
        </div>
    </form>
</div>
@endsection
