<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tidak Ditemukan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class='h-screen flex flex-col items-center justify-center bg-gray-50'>
        <h1 class='text-4xl font-bold text-gray-300 mb-4'>404</h1>
        <p class='text-gray-600 mb-6'>{{ $message ?? 'Halaman tidak ditemukan.' }}</p>
        <a href='{{ $back_url ?? url('/') }}' class='px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition'>Kembali</a>
    </div>
</body>
</html>