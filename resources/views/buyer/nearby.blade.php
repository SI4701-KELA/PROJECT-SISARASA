@extends('layouts.buyer')

@section('title', 'Toko Terdekat')

@section('content')
<div class="max-w-7xl mx-auto" x-data="geoHandler()">
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-8">
        <div class="w-12 h-12 rounded-2xl bg-red-50 flex items-center justify-center text-terracotta shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Radar Toko Terdekat</h1>
            <p class="text-sm text-gray-500 font-medium mt-1">Temukan makanan surplus di sekeliling Anda berdasarkan titik lokasi akurat saat ini.</p>
        </div>
    </div>

    @if(!$hasLocation)
        {{-- State: Belum Ada Lokasi (Loading / Meminta Izin) --}}
        <div class="bg-white rounded-[32px] border border-gray-100 shadow-sm p-16 max-w-3xl mx-auto mt-12 relative overflow-hidden text-center" x-init="getLocation()">
            <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-terracotta to-orange-400"></div>
            
            <div class="relative flex items-center justify-center mb-10">
                <div class="absolute w-32 h-32 rounded-full border-4 border-red-100 opacity-75 animate-ping"></div>
                <div class="absolute w-24 h-24 rounded-full border-4 border-red-200 opacity-50 animate-ping" style="animation-delay: 0.3s;"></div>
                <div class="w-20 h-20 bg-terracotta rounded-full z-10 shadow-lg shadow-red-200 flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>

            <h3 class="text-2xl font-bold text-gray-900 mb-3" x-show="!locationDenied">Mencari Lokasi Anda...</h3>
            <p class="text-gray-500 font-medium mb-8" x-show="!locationDenied">Kami sedang mencari toko UMKM dengan makanan surplus terdekat dari titik Anda.</p>
            
            <div class="inline-block border border-yellow-200 bg-yellow-50 text-yellow-700 px-6 py-3 rounded-full text-sm font-semibold" x-show="!locationDenied">
                Harap "Izinkan / Allow" permintaan akses lokasi pada popup browser Anda.
            </div>

            <div x-show="locationDenied" style="display: none;">
                <h3 class="text-2xl font-bold text-red-600 mb-3">Akses Lokasi Ditolak</h3>
                <p class="text-gray-500 font-medium mb-6">Kami membutuhkan akses lokasi untuk menjalankan Radar. Mohon ubah pengaturan browser Anda.</p>
                <button @click="getLocation()" class="bg-terracotta hover:bg-[#a6402d] text-white font-bold py-3 px-8 rounded-xl shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-terracotta text-sm">
                    Coba Deteksi Lagi
                </button>
            </div>
        </div>
    @else
        {{-- State: Lokasi Ditemukan & Hasil Pencarian --}}
        <div class="bg-red-50/50 border border-red-100 rounded-[20px] p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-terracotta shadow-sm shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-gray-900 font-bold text-sm">Titik Lokasi Anda Ditemukan</h3>
                    <p class="text-gray-600 font-semibold text-sm mt-0.5">Menampilkan <span class="text-terracotta font-bold">{{ $sellers->count() }} Toko</span> terdekat di sekitar Anda.</p>
                </div>
            </div>
            <button @click="getLocation()" class="flex items-center justify-center gap-2 px-5 py-2.5 bg-white border border-terracotta text-terracotta font-bold rounded-xl text-sm hover:bg-red-50 transition-colors shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Perbarui Titik Lokasi
            </button>
        </div>

        @if($sellers->isEmpty())
            <div class="text-center py-20 bg-white rounded-[32px] border border-gray-100 shadow-sm">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Tidak Ada Toko Terdekat</h3>
                <p class="text-gray-500 font-medium">Coba perluas pencarian atau perbarui titik lokasi Anda.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($sellers as $seller)
                    @php
                        $isVeryClose = isset($seller->distance) && $seller->distance <= 5;
                    @endphp
                    <div class="bg-white rounded-[24px] overflow-hidden shadow-sm flex flex-col transition-all duration-300 {{ $isVeryClose ? 'border-2 border-orange-400 ring-4 ring-orange-50 hover:-translate-y-1 hover:shadow-xl hover:shadow-orange-100' : 'border border-gray-100 hover:shadow-md' }}">
                        {{-- Header --}}
                        <div class="{{ $isVeryClose ? 'bg-gradient-to-r from-terracotta to-orange-500' : 'bg-[#1e9d8b]' }} p-5 relative">
                            @if(isset($seller->distance))
                                <div class="absolute top-4 right-4 flex flex-col items-end gap-1.5">
                                    <div class="bg-white {{ $isVeryClose ? 'text-terracotta' : 'text-[#1e9d8b]' }} font-black text-[10px] px-2.5 py-1 rounded-full shadow-sm">
                                        {{ number_format($seller->distance, 1, ',', '.') }} KM
                                    </div>
                                    @if($isVeryClose)
                                        <div class="bg-yellow-300 text-yellow-900 font-black text-[8px] px-2 py-0.5 rounded-full shadow-sm flex items-center gap-1 animate-pulse uppercase tracking-wider">
                                            🔥 Super Dekat
                                        </div>
                                    @endif
                                </div>
                            @endif
                            <h2 class="text-white font-bold text-lg mb-1 pr-16 truncate">{{ $seller->store_name ?? 'Toko Default' }}</h2>
                            <p class="text-white/80 text-sm font-medium mb-3 truncate pr-16">{{ $seller->address ?? '-' }}</p>
                            <div class="flex items-center text-white/90 text-xs font-semibold">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Jam: {{ $seller->open_time ? date('H:i', strtotime($seller->open_time)) : '--:--' }} - {{ $seller->close_time ? date('H:i', strtotime($seller->close_time)) : '--:--' }}
                            </div>
                        </div>

                        {{-- Catalog Section --}}
                        <div class="p-5 flex-1 flex flex-col bg-gray-50/50">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 border-b border-gray-200 pb-2">Katalog UMKM Ini</p>
                            
                            <div class="space-y-3 flex-1">
                                @php
                                    $previewProducts = $seller->products->take(3);
                                    $remainingCount = $seller->products->count() - 3;
                                @endphp

                                @forelse($previewProducts as $product)
                                    @php
                                        $hasDiscount = $product->discount && $product->discount->is_active;
                                    @endphp
                                    <div class="bg-white rounded-xl p-2.5 border border-gray-100 flex items-center gap-3 shadow-sm">
                                        <div class="w-12 h-12 rounded-lg bg-gray-100 shrink-0 overflow-hidden">
                                            @if($product->image)
                                                <img src="{{ Storage::url($product->image) }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full bg-[#1e9d8b]/20"></div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-sm font-bold text-gray-900 truncate mb-0.5">{{ $product->name }}</h4>
                                            @if($hasDiscount)
                                                <div class="flex items-center gap-2">
                                                    <span class="text-gray-400 text-xs font-medium line-through">Rp {{ number_format($product->base_price, 0, ',', '.') }}</span>
                                                    <span class="text-red-500 font-black text-xs">Rp {{ number_format($product->discount->discount_price, 0, ',', '.') }}</span>
                                                    <span class="bg-red-50 text-red-500 text-[8px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded">PROMO SISA RASA</span>
                                                </div>
                                            @else
                                                <p class="text-gray-600 font-bold text-xs">Rp {{ number_format($product->base_price, 0, ',', '.') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500 italic text-center py-4">Belum ada produk</p>
                                @endforelse

                                @if($remainingCount > 0)
                                    <a href="{{ route('buyer.menu') }}" class="block text-center text-xs font-bold text-[#1e9d8b] hover:text-[#157a6c] mt-4">
                                        +{{ $remainingCount }} produk lainnya
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="p-4 bg-white border-t border-gray-100 flex gap-3 items-center">
                            <form method="POST" action="{{ route('buyer.favorite.toggle') }}" class="shrink-0">
                                @csrf
                                <input type="hidden" name="seller_id" value="{{ $seller->id }}">
                                <button type="submit" class="w-11 h-11 rounded-xl border {{ in_array($seller->id, $userFavorites ?? []) ? 'bg-red-50 border-red-100' : 'bg-white border-gray-200 hover:bg-gray-50' }} flex items-center justify-center transition-colors">
                                    @if(in_array($seller->id, $userFavorites ?? []))
                                        <svg class="w-5 h-5 text-[#ff5252] fill-current" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                                    @else
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                    @endif
                                </button>
                            </form>
                            <a href="{{ route('buyer.menu') }}" class="flex-1 text-center py-2.5 bg-white border-2 border-[#1e9d8b] text-[#1e9d8b] font-bold rounded-xl text-sm hover:bg-[#e4f4f2] transition-colors">
                                Kunjungi Toko
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>

@push('scripts')
<script>
    function geoHandler() {
        return {
            locationDenied: false,
            
            getLocation() {
                this.locationDenied = false;

                if ("geolocation" in navigator) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            
                            const url = new URL(window.location.href);
                            url.searchParams.set('lat', lat);
                            url.searchParams.set('lng', lng);
                            
                            window.location.href = url.toString();
                        },
                        (error) => {
                            if (error.code === 1) {
                                this.locationDenied = true;
                            } else {
                                alert("Terjadi kesalahan saat mendeteksi lokasi: " + error.message);
                            }
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 0
                        }
                    );
                } else {
                    alert("Fitur Geolocation tidak didukung oleh browser Anda.");
                }
            }
        };
    }
</script>
@endpush
@endsection
