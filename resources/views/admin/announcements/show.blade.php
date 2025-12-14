<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengumuman - {{ $announcement->recipient }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        /* Agar teks yang panjang tidak keluar container */
        .break-words { word-wrap: break-word; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased">

    <div class="max-w-4xl mx-auto px-4 py-10 min-h-screen">
        
        {{-- Tombol Kembali --}}
        <div class="mb-6">
            <a href="javascript:history.back()" class="inline-flex items-center text-gray-500 hover:text-blue-600 transition font-medium">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
            
            {{-- Header Gradient --}}
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-8 py-6">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="inline-block px-3 py-1 bg-white/20 text-white rounded-full text-xs font-bold tracking-wide uppercase backdrop-blur-sm mb-3">
                            Kepada: {{ $announcement->recipient }}
                        </span>
                        <h1 class="text-2xl md:text-3xl font-bold text-white leading-tight">
                            Pengumuman Sekolah
                        </h1>
                    </div>
                    {{-- Tanggal Besar (Desktop) --}}
                    <div class="text-right text-white/80 hidden md:block">
                        <div class="flex flex-col items-end">
                            <span class="text-3xl font-bold text-white">{{ $formattedDate['day'] }}</span>
                            <span class="uppercase text-xs tracking-wider">{{ $formattedDate['month_year'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tanggal (Mobile) --}}
            <div class="px-8 py-4 bg-gray-50 border-b border-gray-100 flex items-center text-sm text-gray-500 md:hidden">
                <i class="far fa-clock mr-2"></i>
                {{ $formattedDate['full'] }}, Pukul {{ $formattedDate['time'] }} WIB
            </div>

            <div class="p-8">
                {{-- Metadata Tanggal (Desktop) --}}
                <div class="hidden md:flex items-center text-sm text-gray-400 mb-6 pb-4 border-b border-gray-100">
                    <div class="flex items-center mr-6">
                        <i class="far fa-calendar-alt mr-2"></i>
                        {{ $formattedDate['full'] }}
                    </div>
                    <div class="flex items-center">
                        <i class="far fa-clock mr-2"></i>
                        {{ $formattedDate['time'] }} WIB
                    </div>
                </div>

                {{-- Isi Pesan --}}
                <div class="prose max-w-none text-gray-800 leading-relaxed text-lg whitespace-pre-wrap font-normal break-words">
                    {{ $announcement->message }}
                </div>

                {{-- Bagian Lampiran --}}
                @if ($announcement->attachment_file || $announcement->attachment_link)
                    <div class="mt-10 pt-6 border-t border-gray-100">
                        <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">
                            Lampiran & Tautan
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            
                            {{-- File Download --}}
                            @if ($announcement->attachment_file)
                                <a href="{{ asset('uploads/' . $announcement->attachment_file) }}" target="_blank" download class="group flex items-center p-4 bg-white border border-gray-200 rounded-xl hover:border-green-400 hover:shadow-md hover:shadow-green-500/10 transition duration-200">
                                    <div class="w-12 h-12 flex-shrink-0 rounded-full bg-green-50 text-green-600 flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                                        <i class="fas fa-file-download text-xl"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <p class="text-sm font-bold text-gray-800 group-hover:text-green-700 transition">Download Dokumen</p>
                                        <p class="text-xs text-gray-500 truncate mt-1">{{ $cleanFileName }}</p>
                                    </div>
                                </a>
                            @endif

                            {{-- Link Eksternal --}}
                            @if ($announcement->attachment_link)
                                <a href="{{ $announcement->attachment_link }}" target="_blank" class="group flex items-center p-4 bg-white border border-gray-200 rounded-xl hover:border-blue-400 hover:shadow-md hover:shadow-blue-500/10 transition duration-200">
                                    <div class="w-12 h-12 flex-shrink-0 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                                        <i class="fas fa-link text-xl"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <p class="text-sm font-bold text-gray-800 group-hover:text-blue-700 transition">Buka Tautan</p>
                                        <p class="text-xs text-gray-500 truncate mt-1">{{ $announcement->attachment_link }}</p>
                                    </div>
                                    <i class="fas fa-external-link-alt ml-auto text-gray-300 group-hover:text-blue-400 text-xs"></i>
                                </a>
                            @endif

                        </div>
                    </div>
                @endif

            </div>
            
            {{-- Footer ID --}}
            <div class="bg-gray-50 px-8 py-4 border-t border-gray-100 flex justify-between items-center">
                <span class="text-xs text-gray-400">ID Pengumuman: #{{ $announcement->id }}</span>
                <span class="text-xs text-gray-400">Sistem Informasi Sekolah</span>
            </div>
        </div>

    </div>

</body>
</html>