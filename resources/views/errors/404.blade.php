<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Sayfa Bulunamadı</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
<header class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
        <img src="{{ asset('/assets/images/logo.png') }}" alt="Berka İş Güvenliği Logo" class="h-10">
    </div>
</header>

<main class="flex-grow flex items-center justify-center p-4">
    <div class="max-w-lg w-full bg-white rounded-lg shadow-xl p-8 text-center">
        <div class="mb-8">
            <svg class="mx-auto h-24 w-24 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1 class="text-4xl font-bold text-gray-800 mb-4">404</h1>
        <h2 class="text-2xl font-semibold text-gray-600 mb-4">Sayfa Bulunamadı</h2>
        <p class="text-gray-600 mb-8">Üzgünüz, aradığınız sayfayı bulamadık. Adresi kontrol edin veya ana sayfaya dönün.</p>
        <div class="space-y-4">
            <a href="/"
               class="inline-block bg-indigo-500 hover:bg-indigo-600 text-white font-semibold py-2 px-4 rounded-lg transition duration-300 ease-in-out transform hover:scale-105">
                Ana Sayfaya Dön
            </a>
        </div>
        <div class="mt-8 text-sm text-gray-500">
            <p>Eğer bu bir hata olduğunu düşünüyorsanız, <br>lütfen bizimle iletişime geçin.<br>
                <a href="mailto:hello@expozy.co" class="text-indigo-500 hover:underline">hello@expozy.co</a>
            </p>
        </div>
    </div>
</main>

<footer class="bg-white shadow-sm mt-auto">
    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 text-center text-gray-500 text-sm">
        &copy; {{ date('Y') }} Berka İş Güvenliği. Tüm hakları saklıdır.
    </div>
</footer>

<script>
    console.log('404 Hata: Sayfa bulunamadı - ' + window.location.href);
</script>
</body>
</html>
