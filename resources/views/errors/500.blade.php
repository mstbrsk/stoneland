<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Sunucu Hatası</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">
<div class="max-w-lg w-full bg-white rounded-lg shadow-xl p-8 text-center">
    <div class="mb-8">
        <svg class="mx-auto h-16 w-16 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
    </div>
    <h1 class="text-3xl font-bold text-gray-800 mb-4">500 - Sunucu Hatası</h1>
    <p class="text-gray-600 mb-8">Üzgünüz, bir şeyler yanlış gitti. Ekibimiz bu sorunu çözmek için çalışıyor.</p>
    <div class="space-y-4">
        <button onclick="window.location.reload()" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg transition duration-300 ease-in-out transform hover:scale-105">
            Sayfayı Yenile
        </button>
        <button onclick="window.history.back()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded-lg transition duration-300 ease-in-out transform hover:scale-105">
            Önceki Sayfaya Dön
        </button>
    </div>
    <div class="mt-8 text-sm text-gray-500">
        <p>Sorun devam ederse, lütfen sistem yöneticinizle iletişime geçin.</p>
        <p class="mt-2">Hata Kodu: 500</p>
    </div>
</div>

<script>
    // Otomatik yenileme için geri sayım
    let countdown = 30;
    const countdownElement = document.createElement('p');
    countdownElement.className = 'mt-4 text-sm text-gray-500';
    document.querySelector('.max-w-lg').appendChild(countdownElement);

    function updateCountdown() {
        countdownElement.textContent = `Sayfa ${countdown} saniye içinde otomatik olarak yenilenecek.`;
        if (countdown <= 0) {
            window.location.reload();
        } else {
            countdown--;
            setTimeout(updateCountdown, 1000);
        }
    }

    updateCountdown();
</script>
</body>
</html>
