<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sisa Rasa - Selamatkan Makanan, Nikmati Harga Sisa</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #FAFAFA; color: #111827; }
        .text-terracotta { color: #C0392B; }
        .bg-terracotta { background-color: #C0392B; }
        .hover-bg-terracotta:hover { background-color: #A93226; }
        .border-terracotta { border-color: #C0392B; }
    </style>
</head>
<body class="antialiased selection:bg-red-100 selection:text-red-900">

    <!-- Navbar -->
    <nav class="fixed w-full z-50 bg-white/90 backdrop-blur-sm border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 flex justify-between items-center h-20">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-terracotta rounded-lg flex items-center justify-center text-white font-bold text-xl leading-none">S</div>
                <span class="text-xl font-bold tracking-tight text-gray-900">Sisa Rasa</span>
            </div>
            <div class="flex items-center gap-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-gray-700 hover:text-terracotta transition-colors">Ke Dasbor</a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition-colors hidden sm:block">Masuk</a>
                    <a href="{{ route('register') }}" class="bg-gray-900 text-white text-sm font-semibold px-5 py-2.5 rounded-lg hover:bg-gray-800 transition-colors">Mulai Sekarang</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Hero Section (Split Layout) -->
    <section class="pt-52 pb-20 lg:pt-64 lg:pb-28 px-6 lg:px-8">
        <div class="max-w-7xl mx-auto flex flex-col lg:flex-row items-center gap-12 lg:gap-8">
            <!-- Kiri: Teks -->
            <div class="w-full lg:w-1/2">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-[1.15] mb-6 tracking-tight">
                    Menyelamatkan Makanan,<br>
                    <span class="text-terracotta">Satu Porsi Setiap Harinya.</span>
                </h1>
                <p class="text-lg text-gray-500 font-normal leading-relaxed mb-8 max-w-lg">
                    Platform yang menghubungkan Anda dengan restoran dan UMKM lokal untuk menyelamatkan makanan berlebih yang masih sangat layak konsumsi dengan harga miring. 
                </p>
                <div class="flex flex-col sm:flex-row items-start gap-4">
                    <a href="{{ route('register') }}" class="w-full sm:w-auto bg-terracotta text-white px-8 py-3.5 rounded-xl font-semibold transition-all duration-300 transform hover:-translate-y-1.5 hover:shadow-xl hover:bg-[#A93226] hover:shadow-red-200 text-center">
                        Eksplorasi Makanan
                    </a>
                    <a href="{{ route('register') }}" class="w-full sm:w-auto bg-white border border-gray-200 text-gray-700 px-8 py-3.5 rounded-xl font-semibold transition-all duration-300 transform hover:-translate-y-1.5 hover:shadow-xl hover:border-gray-300 hover:bg-gray-50 text-center">
                        Daftar Jadi Penjual
                    </a>
                </div>
            </div>

            <!-- Kanan: Gambar -->
            <div class="w-full lg:w-1/2">
                <div class="aspect-[4/3] rounded-3xl overflow-hidden shadow-2xl relative">
                    <img src="https://images.unsplash.com/photo-1543339308-43e59d6b73a6?q=80&w=2070&auto=format&fit=crop" class="w-full h-full object-cover" alt="Sisa Rasa Food">
                    <!-- Label Diskon di atas gambar -->
                    <div class="absolute bottom-6 left-6 bg-white/90 backdrop-blur-sm px-4 py-2 rounded-lg font-bold text-terracotta shadow-lg flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Tersedia Saat "Jam Diskon"
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bar Statistik (Melayang di antara section) -->
    <section class="px-6 lg:px-8 relative z-10 -mt-10 lg:-mt-14 mb-20">
        <div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center divide-y md:divide-y-0 md:divide-x divide-gray-100">
                <div class="pt-4 md:pt-0">
                    <p class="text-4xl font-extrabold text-gray-900 mb-1">{{ number_format($totalFoodSaved, 0, ',', '.') }}</p>
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Porsi Diselamatkan</p>
                </div>
                <div class="pt-4 md:pt-0">
                    <p class="text-4xl font-extrabold text-gray-900 mb-1">{{ number_format($carbonSaved, 1, ',', '.') }} <span class="text-lg font-bold text-terracotta">kg</span></p>
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">CO2 Dicegah</p>
                </div>
                <div class="pt-4 md:pt-0">
                    <p class="text-4xl font-extrabold text-gray-900 mb-1">{{ number_format($totalUmkm, 0, ',', '.') }}</p>
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Mitra UMKM</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Tata Cara Pemesanan -->
    <section class="py-20 px-6 lg:px-8 bg-white border-t border-gray-100">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-extrabold text-gray-900 mb-4">Cara Kerja Sisa Rasa</h2>
                <p class="text-gray-500 font-medium text-lg">Tiga langkah mudah untuk mulai menyelamatkan makanan.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 max-w-5xl mx-auto">
                <!-- Langkah 1 -->
                <div class="bg-gray-50 p-8 rounded-3xl text-center border border-gray-100">
                    <div class="w-14 h-14 bg-white text-terracotta rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">1. Temukan Toko</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        Cari toko di sekitarmu yang sedang membuka "Jam Diskon" menjelang jam tutup operasional mereka.
                    </p>
                </div>

                <!-- Langkah 2 -->
                <div class="bg-gray-50 p-8 rounded-3xl text-center border border-gray-100">
                    <div class="w-14 h-14 bg-white text-terracotta rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">2. Pesan & Bayar</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        Pilih menu makanan surplus yang tersedia dan lakukan pembayaran secara praktis melalui QRIS di aplikasi.
                    </p>
                </div>

                <!-- Langkah 3 -->
                <div class="bg-gray-50 p-8 rounded-3xl text-center border border-gray-100">
                    <div class="w-14 h-14 bg-white text-terracotta rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">3. Ambil Pesanan</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        Kunjungi toko sesuai waktu yang ditentukan, tunjukkan bukti pesanan, dan selamat menikmati!
                    </p>
                </div>
            </div>
            
            <div class="mt-16 text-center">
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 text-terracotta font-semibold bg-red-50 px-6 py-3 rounded-full transition-all duration-300 transform hover:-translate-y-1 hover:shadow-md hover:bg-red-100 hover:text-red-800">
                    Coba Pesan Sekarang &rarr;
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-[#FAFAFA] py-8 text-center border-t border-gray-200">
        <p class="text-gray-400 text-sm font-medium">&copy; {{ date('Y') }} Sisa Rasa. Hak Cipta Dilindungi.</p>
    </footer>

</body>
</html>
