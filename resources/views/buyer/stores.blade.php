@component('layouts.buyer')
    @slot('header')
        <h2 class="font-semibold text-xl leading-tight text-gray-800">
            {{ __('Daftar UMKM Mitra') }}
        </h2>
    @endslot
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Header -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <p class="mt-3 text-gray-600 max-w-2xl text-lg">Temukan berbagai hidangan lezat dan berkualitas dari mitra UMKM Sisa Rasa di sekitar Anda.</p>
            </div>
        </div>

        {{-- Flash Message --}}
        @if(session('success'))
            <div class="mb-6 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-medium flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
            </div>
        @endif

        <!-- Cek Kosong -->
        @if($sellers->count() == 0)
            <div class="bg-gradient-to-b from-orange-50 to-white border border-orange-100 rounded-3xl p-16 text-center shadow-sm">
                <div class="w-24 h-24 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-3">Belum ada UMKM Sisa Rasa yang terdaftar.</h3>
                <p class="text-gray-500 max-w-md mx-auto text-lg">Saat ini belum ada mitra UMKM yang berstatus aktif. Silakan kembali lagi nanti untuk melihat katalog menarik kami.</p>
            </div>
        @else
            <!-- Grid Katalog -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($sellers as $seller)
                    <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 flex flex-col group relative">
                        {{-- Tombol Hati Favorit --}}
                        <form method="POST" action="{{ route('buyer.favorite.toggle') }}" class="absolute top-4 right-4 z-10">
                            @csrf
                            <input type="hidden" name="seller_id" value="{{ $seller->id }}">
                            <button type="submit" class="group/heart w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 shadow-md hover:scale-110 active:scale-95 {{ in_array($seller->id, $userFavorites ?? []) ? 'bg-red-50 hover:bg-red-100' : 'bg-white/90 backdrop-blur-sm hover:bg-red-50' }}" title="{{ in_array($seller->id, $userFavorites ?? []) ? 'Hapus dari Favorit' : 'Tambahkan ke Favorit' }}">
                                @if(in_array($seller->id, $userFavorites ?? []))
                                    {{-- Hati Penuh (Sudah Difavoritkan) --}}
                                    <svg class="w-5 h-5 text-red-500 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/>
                                    </svg>
                                @else
                                    {{-- Hati Kosong (Belum Difavoritkan) --}}
                                    <svg class="w-5 h-5 text-gray-400 group-hover/heart:text-red-400 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                                    </svg>
                                @endif
                            </button>
                        </form>

                        <div class="p-6 flex-grow">
                            <div class="flex items-start gap-5">
                                <!-- Foto Toko -->
                                <div class="shrink-0">
                                    @if($seller->store_photo)
                                        <img src="{{ asset('storage/' . $seller->store_photo) }}" alt="{{ $seller->store_name }}" class="w-16 h-16 rounded-2xl object-cover shadow-sm ring-1 ring-gray-100 group-hover:scale-105 transition-transform duration-300">
                                    @else
                                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-orange-400 to-orange-500 flex items-center justify-center text-white text-2xl font-bold shadow-sm ring-1 ring-orange-200 group-hover:scale-105 transition-transform duration-300">
                                            {{ strtoupper(substr($seller->store_name ?? 'U', 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Info Toko -->
                                <div class="flex-1 min-w-0 pr-8">
                                    <h2 class="text-xl font-bold text-gray-900 mb-1.5 truncate group-hover:text-orange-600 transition-colors">{{ $seller->store_name ?? 'Nama Toko Tidak Tersedia' }}</h2>
                                    <p class="text-sm text-gray-500 line-clamp-2 flex items-start gap-1.5">
                                        <svg class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span class="leading-snug">{{ $seller->address ?? 'Alamat tidak tersedia' }}</span>
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Badges -->
                            <div class="mt-6 flex flex-wrap gap-2.5">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-orange-50 text-orange-700 border border-orange-100">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $seller->opening_time ? date('H:i', strtotime($seller->opening_time)) : '--:--' }} - {{ $seller->closing_time ? date('H:i', strtotime($seller->closing_time)) : '--:--' }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                    </svg>
                                    {{ $seller->products_count }} Menu
                                </span>
                            </div>
                        </div>
                        
                        <!-- Action Button -->
                        <div class="p-4 bg-gray-50 border-t border-gray-100">
                            <a href="{{ route('buyer.menu') }}" class="flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-white hover:bg-orange-50 text-orange-600 font-semibold rounded-xl border-2 border-orange-100 hover:border-orange-500 transition-all duration-300">
                                Lihat Katalog Menu
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endcomponent
