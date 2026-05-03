@component('layouts.buyer')
    @slot('header')
        <h2 class="font-semibold text-xl leading-tight text-gray-800">
            {{ __('Toko Tersimpan') }}
        </h2>
    @endslot

    <div class="container mx-auto px-4 py-8 max-w-7xl">
        {{-- =============================== --}}
        {{--  HEADER SECTION                  --}}
        {{-- =============================== --}}
        <div class="mb-8">
            <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 tracking-tight leading-tight">
                Toko Tersimpan
            </h1>
            <p class="mt-3 text-gray-500 text-lg max-w-2xl">
                Temukan kembali UMKM favoritmu dan selamatkan makanan lezat hari ini.
            </p>
        </div>

        {{-- Flash Message --}}
        @if(session('success'))
            <div class="mb-6 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-medium flex items-center gap-2 animate-fade-in">
                <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- =============================== --}}
        {{--  IMPACT BANNER                   --}}
        {{-- =============================== --}}
        @if($sellers->count() > 0)
            <div class="mb-8 bg-gradient-to-r from-terracotta to-orange-500 rounded-2xl p-6 md:p-8 shadow-lg overflow-hidden relative">
                {{-- Decorative circles --}}
                <div class="absolute -top-8 -right-8 w-32 h-32 bg-white/10 rounded-full"></div>
                <div class="absolute -bottom-4 -left-4 w-20 h-20 bg-white/10 rounded-full"></div>
                
                <div class="relative flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-white font-bold text-xl md:text-2xl mb-1">Dampak Pilihanmu 🌱</h3>
                        <p class="text-white/80 text-sm md:text-base">
                            Kamu telah menyimpan <span class="font-bold text-white">{{ $sellers->count() }} UMKM</span> ke dalam daftar favoritmu. Terus dukung UMKM lokal dan selamatkan makanan berlebih!
                        </p>
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl px-6 py-4 text-center shrink-0">
                        <p class="text-white/70 text-xs font-semibold uppercase tracking-wider">Total Tersimpan</p>
                        <p class="text-white text-3xl font-extrabold">{{ $sellers->count() }}</p>
                        <p class="text-white/60 text-xs">UMKM Favorit</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- =============================== --}}
        {{--  EMPTY STATE                     --}}
        {{-- =============================== --}}
        @if($sellers->count() == 0)
            <div class="bg-gradient-to-b from-orange-50/80 to-white border border-orange-100 rounded-3xl p-12 md:p-20 text-center shadow-sm">
                {{-- Animated Heart Icon --}}
                <div class="relative w-28 h-28 mx-auto mb-8">
                    <div class="absolute inset-0 bg-red-100 rounded-full animate-ping opacity-20"></div>
                    <div class="relative w-28 h-28 bg-gradient-to-br from-red-50 to-orange-50 rounded-full flex items-center justify-center shadow-sm">
                        <svg class="w-14 h-14 text-red-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl md:text-3xl font-extrabold text-gray-900 mb-3">Belum Ada Toko Tersimpan</h3>
                <p class="text-gray-500 max-w-md mx-auto text-base md:text-lg mb-8">
                    Jelajahi UMKM mitra Sisa Rasa dan ketuk ikon hati untuk menyimpan toko favoritmu di sini.
                </p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ route('buyer.stores') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-terracotta hover:bg-terracotta/90 text-white font-bold rounded-xl shadow-md hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.15c0 .415.336.75.75.75z"/></svg>
                        Jelajahi Semua UMKM
                    </a>
                    <a href="{{ route('buyer.nearby') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white hover:bg-orange-50 text-terracotta font-bold rounded-xl border-2 border-terracotta/20 hover:border-terracotta shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                        Cari Toko Terdekat
                    </a>
                </div>
            </div>
        @else
            {{-- =============================== --}}
            {{--  GRID CARDS                      --}}
            {{-- =============================== --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($sellers as $seller)
                    <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 flex flex-col group relative">
                        
                        {{-- Gambar Toko --}}
                        <div class="relative h-48 w-full overflow-hidden bg-gradient-to-br from-gray-100 to-gray-200">
                            @if($seller->store_photo)
                                <img src="{{ asset('storage/' . $seller->store_photo) }}" 
                                     alt="{{ $seller->store_name }}" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-orange-100 to-orange-200">
                                    <div class="text-center">
                                        <div class="w-16 h-16 mx-auto bg-white/60 rounded-2xl flex items-center justify-center mb-2">
                                            <span class="text-3xl font-extrabold text-orange-500">{{ strtoupper(substr($seller->store_name ?? 'U', 0, 1)) }}</span>
                                        </div>
                                        <p class="text-orange-600/60 text-xs font-medium">Foto belum tersedia</p>
                                    </div>
                                </div>
                            @endif

                            {{-- Gradient Overlay --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>

                            {{-- Badge Ketersediaan Surplus --}}
                            <div class="absolute bottom-3 left-3">
                                @if(($seller->total_surplus ?? 0) > 0)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-terracotta text-white shadow-md backdrop-blur-sm">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="4"/></svg>
                                        {{ $seller->total_surplus }} Sisa Tersedia
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-gray-500/80 text-white shadow-md backdrop-blur-sm">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                        Habis Terjual
                                    </span>
                                @endif
                            </div>

                            {{-- Tombol Hati Favorit --}}
                            <form method="POST" action="{{ route('buyer.favorite.toggle') }}" class="absolute top-3 right-3 z-10">
                                @csrf
                                <input type="hidden" name="seller_id" value="{{ $seller->id }}">
                                <button type="submit" class="group/heart w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 shadow-lg hover:scale-110 active:scale-95 bg-white/90 backdrop-blur-sm hover:bg-red-50" title="Hapus dari Favorit">
                                    <svg class="w-5 h-5 text-red-500 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/>
                                    </svg>
                                </button>
                            </form>
                        </div>

                        {{-- Detail Toko --}}
                        <div class="p-5 flex-grow">
                            <div class="flex items-start justify-between mb-3">
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-lg font-bold text-gray-900 truncate group-hover:text-terracotta transition-colors duration-200">
                                        {{ $seller->store_name ?? 'Nama Toko' }}
                                    </h3>
                                    @if($seller->products && $seller->products->count() > 0 && $seller->products->first()->category)
                                        <p class="text-sm text-terracotta font-semibold mt-0.5">
                                            {{ $seller->products->first()->category->name }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            {{-- Alamat --}}
                            <p class="text-sm text-gray-500 line-clamp-2 flex items-start gap-1.5 mb-4">
                                <svg class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="leading-snug">{{ $seller->address ?? 'Alamat tidak tersedia' }}</span>
                            </p>

                            {{-- Info Badges --}}
                            <div class="flex flex-wrap gap-2">
                                {{-- Status Buka/Tutup --}}
                                @php
                                    $now = now()->format('H:i:s');
                                    $isOpen = $seller->open_time && $seller->close_time && $now >= $seller->open_time && $now <= $seller->close_time;
                                @endphp
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold {{ $isOpen ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-gray-100 text-gray-500 border border-gray-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $isOpen ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                    {{ $isOpen ? 'Buka' : 'Tutup' }}
                                </span>
                                
                                {{-- Jam Operasi --}}
                                @if($seller->open_time && $seller->close_time)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-orange-50 text-orange-700 border border-orange-100">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ date('H:i', strtotime($seller->open_time)) }} - {{ date('H:i', strtotime($seller->close_time)) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Action Footer --}}
                        <div class="p-4 bg-gray-50/50 border-t border-gray-100">
                            <a href="{{ route('buyer.menu') }}" class="flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-white hover:bg-terracotta hover:text-white text-terracotta font-semibold rounded-xl border-2 border-terracotta/20 hover:border-terracotta transition-all duration-300 group/btn">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                Lihat Katalog Menu
                                <svg class="w-4 h-4 group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                @endforeach

                {{-- =============================== --}}
                {{--  CARD "TAMBAH TOKO LAIN"         --}}
                {{-- =============================== --}}
                <a href="{{ route('buyer.stores') }}" class="bg-gradient-to-b from-white to-orange-50/50 rounded-2xl border-2 border-dashed border-orange-200 hover:border-terracotta flex flex-col items-center justify-center p-8 transition-all duration-300 hover:shadow-lg group min-h-[320px]">
                    <div class="w-16 h-16 rounded-2xl bg-orange-100 group-hover:bg-terracotta flex items-center justify-center transition-all duration-300 mb-4 group-hover:scale-110 group-hover:shadow-lg">
                        <svg class="w-8 h-8 text-orange-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    </div>
                    <h4 class="text-lg font-bold text-gray-700 group-hover:text-terracotta transition-colors mb-1">Tambah Toko Lain</h4>
                    <p class="text-sm text-gray-400 text-center max-w-[180px]">Eksplor UMKM baru di sekitarmu</p>
                </a>
            </div>
        @endif
    </div>

    <style>
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fade-in 0.4s ease-out;
        }
    </style>
@endcomponent
