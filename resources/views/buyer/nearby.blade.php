<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sisa Rasa - Lokasi Terdekat</title>
    <!-- Asumsi menggunakan Vite untuk kompilasi CSS Tailwind dan JS Alpine -->
    <!-- Jika Alpine belum ada, pastikan project Laravel sudah menginclude AlpineJS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Fallback Alpine.js via CDN jika belum terkonfigurasi di resources/js/app.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased">
    
    <!-- Container utama dengan state Alpine.js -->
    <div x-data="geoHandler()" class="min-h-screen relative">
        
        <!-- Menu Header -->
        <header class="bg-white shadow-sm sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
                <h1 class="text-xl font-bold text-gray-900">Toko Terdekat</h1>
                
                @if(!$hasLocation)
                    <!-- Tombol jika belum ada lokasi di URL -->
                    <button @click="getLocation()" 
                            class="px-4 py-2 text-white font-medium rounded-lg shadow-sm transition-colors duration-200" 
                            style="background-color: #c04b36;"
                            onmouseover="this.style.backgroundColor='#a33d2a'" 
                            onmouseout="this.style.backgroundColor='#c04b36'">
                        Deteksi Lokasi Saya
                    </button>
                @else
                    <!-- Tombol jika lokasi sudah ada di URL -->
                    <button @click="getLocation()" 
                            class="px-4 py-2 bg-white text-[#c04b36] border border-[#c04b36] font-medium rounded-lg shadow-sm hover:bg-[#c04b36] hover:text-white transition-colors duration-200">
                        Perbarui Titik Lokasi
                    </button>
                @endif
            </div>
        </header>

        <!-- Status Ditolak (Error Panel) -->
        <div x-show="locationDenied"
             style="display: none;" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform -translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             class="max-w-3xl mx-auto mt-6 px-4">
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <!-- Icon Alert -->
                        <svg class="h-6 w-6 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-semibold text-red-800">Akses Lokasi Ditolak</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p>Mohon Izinkan Lokasi Anda via pengaturan gembok URL browser. Kami membutuhkan lokasi Anda untuk menampilkan penjual "Sisa Rasa" terdekat.</p>
                        </div>
                        <div class="mt-4">
                            <button @click="getLocation()" class="bg-red-100 text-red-800 hover:bg-red-200 px-3 py-1.5 rounded-md text-sm font-medium transition-colors">
                                Coba Akses Lagi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content (Daftar Toko / Mockup) -->
        <main class="max-w-7xl mx-auto px-4 py-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Data Toko Loop -->
                @forelse($sellers as $seller)
                    <div class="bg-gradient-to-br from-white to-[#009688]/10 rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow relative overflow-hidden">
                        
                        <!-- Badge Jarak (Haversine) -->
                        @if(isset($seller->distance))
                            <div class="bg-white text-[#009688] font-bold px-3 py-1 rounded-bl-lg absolute top-0 right-0 shadow-sm border-l border-b border-gray-100">
                                {{ number_format($seller->distance, 1) }} KM
                            </div>
                        @endif

                        <h3 class="text-lg font-bold text-gray-800">{{ $seller->name ?? 'Nama Toko Default' }}</h3>
                        <p class="text-gray-500 text-sm mt-1">{{ $seller->address ?? 'Alamat Toko Default' }}</p>
                    </div>
                @empty
                    @if($hasLocation)
                    <div class="col-span-full text-center py-12 text-gray-500 bg-white rounded-xl shadow-sm border border-gray-100">
                        <p class="text-lg">Belum ada data toko Sisa Rasa di sekitar Anda.</p>
                    </div>
                    @else
                    <div class="col-span-full text-center py-16 text-gray-500 bg-white rounded-xl shadow-sm border border-gray-100">
                        <svg class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <p class="text-lg font-medium">Silakan deteksi lokasi terlebih dahulu</p>
                        <p class="mt-1">Temukan beragam makanan Sisa Rasa yang lezat di dekat Anda.</p>
                    </div>
                    @endif
                @endforelse
            </div>
        </main>

        <!-- Animasi Loading (Radar) -->
        <!-- Overlay besar di tengah layar, hanya muncul saat x-show="isLocating" -->
        <div x-show="isLocating" 
             style="display: none;"
             class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-white/90 backdrop-blur-sm transition-opacity">
            
            <div class="relative flex items-center justify-center mb-8">
                <!-- Ring membesar (Ping/Pulse) -->
                <div class="absolute w-32 h-32 rounded-full border-4 border-[#c04b36] opacity-75 animate-ping"></div>
                <div class="absolute w-24 h-24 rounded-full border-4 border-[#c04b36] opacity-50 animate-ping" style="animation-delay: 0.3s;"></div>
                <div class="absolute w-16 h-16 rounded-full border-4 border-[#c04b36] opacity-25 animate-ping" style="animation-delay: 0.6s;"></div>
                <!-- Titik tengah -->
                <div class="w-8 h-8 bg-[#c04b36] rounded-full z-10 shadow-lg flex items-center justify-center">
                    <div class="w-3 h-3 bg-white rounded-full"></div>
                </div>
            </div>
            
            <p class="text-xl font-bold text-gray-800 animate-pulse tracking-wide">Mencari Lokasi Anda...</p>
            <p class="text-sm text-gray-500 mt-2">Mohon tunggu sebentar atau konfirmasi izin di browser.</p>
        </div>
        
    </div>

    <!-- Script Block berisi Objek geoHandler() -->
    <script>
        function geoHandler() {
            return {
                locationDenied: false,
                isLocating: false,

                getLocation() {
                    // Reset state
                    this.locationDenied = false;
                    this.isLocating = true;

                    // Pastikan browser mendukung geolocation
                    if ("geolocation" in navigator) {
                        navigator.geolocation.getCurrentPosition(
                            // 1. Sukses
                            (position) => {
                                const lat = position.coords.latitude;
                                const lng = position.coords.longitude;
                                
                                // Modifikasi URL saat ini dan otomatis lakukan refresh/redirect
                                const url = new URL(window.location.href);
                                url.searchParams.set('lat', lat);
                                url.searchParams.set('lng', lng);
                                
                                window.location.href = url.toString();
                            },
                            // 2. Gagal / Ditolak
                            (error) => {
                                this.isLocating = false;
                                
                                // Error code 1 (PERMISSION_DENIED): User menolak akses lokasi
                                if (error.code === 1) {
                                    this.locationDenied = true;
                                } else {
                                    alert("Terjadi kesalahan saat mendeteksi lokasi: " + error.message);
                                }
                            },
                            // 3. Opsi tambahan
                            {
                                enableHighAccuracy: true,
                                timeout: 10000,
                                maximumAge: 0
                            }
                        );
                    } else {
                        // Browser tidak mendukung
                        this.isLocating = false;
                        alert("Fitur Geolocation tidak didukung oleh browser Anda.");
                    }
                }
            };
        }
    </script>

</body>
</html>
