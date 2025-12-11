<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Eduface</title>
    {{-- Menggunakan CDN sesuai kode asli (bisa diganti Vite jika mau) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');
        body { font-family: 'Roboto', sans-serif; }
        ::placeholder { color: #9CA3AF; font-size: 0.85rem; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-6">

    <div class="w-full max-w-[380px] mx-4 bg-white rounded-lg shadow-xl overflow-hidden">
        
        {{-- Header Biru --}}
        <div class="bg-[#2F80ED] p-6 text-center pb-8">
            <div class="w-16 h-16 bg-white rounded-full mx-auto flex items-center justify-center shadow-md mb-3 relative">
                {{-- Pastikan logo ada di public/assets/logo.png --}}
                <img src="{{ asset('assets/logo.png') }}" alt="logo" class="w-12 h-12 object-contain">
            </div>
            <h1 class="text-white text-lg font-bold">Eduface: Just Face It</h1>
        </div>

        <div class="p-6 pt-4 bg-white relative -mt-5">
            
            <div class="mb-4 text-center md:text-left">
                <h2 class="text-xl font-bold text-gray-900">Masuk ke Akun Anda</h2>
                <p class="text-gray-400 text-xs mt-1">Masukkan kredensial Anda untuk melanjutkan</p>
            </div>

            {{-- Menampilkan Error Login --}}
            @error('login_error')
                <div class="mb-3 text-center text-red-500 text-xs font-medium bg-red-50 py-1.5 rounded">
                    {{ $message }}
                </div>
            @enderror

            <form action="{{ route('login.perform') }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label class="block text-gray-800 text-xs font-bold mb-1" for="username">
                        Email / Username:
                    </label>
                    <input type="text" id="username" name="username" required value="{{ old('username') }}"
                        class="w-full border-b border-gray-300 py-1.5 text-sm text-gray-700 focus:outline-none focus:border-[#2F80ED] transition-colors bg-transparent placeholder-gray-400"
                        placeholder="Masukkan username">
                </div>

                <div class="mb-2">
                    <label class="block text-gray-800 text-xs font-bold mb-1" for="password">
                        Password:
                    </label>
                    <input type="password" id="password" name="password" required 
                        class="w-full border-b border-gray-300 py-1.5 text-sm text-gray-700 focus:outline-none focus:border-[#2F80ED] transition-colors bg-transparent placeholder-gray-400"
                        placeholder="Masukkan password">
                </div>

                <div class="text-right mb-5">
                    <a href="#" class="text-[#2F80ED] text-xs font-bold hover:underline">Lupa Password?</a>
                </div>

                <button type="submit" 
                    class="w-full bg-[#2F80ED] hover:bg-blue-600 text-white font-bold py-2.5 text-sm rounded-lg shadow-md transition duration-200 mb-4">
                    Masuk
                </button>

                <div class="flex items-center justify-between mb-4">
                    <div class="w-full h-px bg-gray-300"></div>
                    <span class="text-gray-400 text-xs px-3">atau</span>
                    <div class="w-full h-px bg-gray-300"></div>
                </div>

                <div class="text-center text-xs text-gray-500">
                    Belum punya akun? <a href="{{ route('register') }}" class="text-[#2F80ED] font-bold hover:underline">Aktivasi Sekarang</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>