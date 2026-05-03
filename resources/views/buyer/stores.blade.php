@extends('layouts.buyer')

@section('title', 'Daftar Toko')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center gap-4 mb-8">
        <div class="w-12 h-12 rounded-2xl bg-red-50 flex items-center justify-center text-terracotta shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        </div>
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Daftar UMKM Mitra</h1>
            <p class="text-sm text-gray-500 font-medium mt-1">Jelajahi seluruh toko yang telah bergabung dalam misi penyelamatan makanan Sisa Rasa.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl shadow-sm font-medium text-sm flex items-center gap-3">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if($sellers->isEmpty())
        <div class="text-center py-20 bg-white rounded-[32px] border border-gray-100 shadow-sm">
            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">Belum ada mitra UMKM</h3>
            <p class="text-gray-500 font-medium">Saat ini belum ada toko yang berstatus aktif.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($sellers as $seller)
                <div class="bg-white rounded-[24px] overflow-hidden shadow-sm border border-gray-100 flex flex-col hover:shadow-md transition-all duration-300 p-6 relative group">
                    
                    {{-- Favorite Toggle --}}
                    <form method="POST" action="{{ route('buyer.favorite.toggle') }}" class="absolute top-6 right-6 z-10">
                        @csrf
                        <input type="hidden" name="seller_id" value="{{ $seller->id }}">
                        <button type="submit" class="focus:outline-none transition-transform hover:scale-110 active:scale-95">
                            @if(in_array($seller->id, $userFavorites ?? []))
                                <svg class="w-6 h-6 text-[#ff5252] fill-current" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                            @else
                                <svg class="w-6 h-6 text-gray-300 hover:text-red-400 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            @endif
                        </button>
                    </form>

                    {{-- Top Section: Avatar & Name --}}
                    <div class="flex items-center gap-4 mb-6">
                        @if($seller->store_photo)
                            <img src="{{ asset('storage/' . $seller->store_photo) }}" alt="{{ $seller->store_name }}" class="w-14 h-14 rounded-full object-cover shadow-sm border border-gray-100">
                        @else
                            <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 font-bold border border-gray-200">
                                {{ strtoupper(substr($seller->store_name ?? 'T', 0, 1)) }}
                            </div>
                        @endif
                        <div class="pr-8">
                            <h2 class="text-lg font-bold text-gray-900 leading-tight mb-1 truncate">{{ $seller->store_name ?? 'Toko' }}</h2>
                            <div class="flex items-center text-[11px] text-gray-500 font-bold">
                                <svg class="w-3.5 h-3.5 text-orange-400 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                4.8 <span class="text-gray-400 font-medium ml-1">(120 Ulasan)</span>
                            </div>
                        </div>
                    </div>

                    {{-- Middle Section: Location & Badges --}}
                    <div class="mb-6">
                        <p class="text-sm text-gray-600 font-semibold mb-3">Lokasi: <span class="font-medium text-gray-500">{{ $seller->address ?? '-' }}</span></p>
                        
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-50 border border-gray-100 rounded-xl text-xs font-semibold text-gray-600">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $seller->open_time ? date('H:i', strtotime($seller->open_time)) : '--:--' }} - {{ $seller->close_time ? date('H:i', strtotime($seller->close_time)) : '--:--' }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 border border-red-100 rounded-xl text-xs font-bold text-terracotta">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                {{ $seller->products_count }} Menu
                            </span>
                        </div>
                    </div>

                    {{-- Bottom Action --}}
                    <div class="mt-auto pt-2">
                        <a href="{{ route('buyer.menu') }}" class="flex items-center justify-center gap-2 w-full py-2.5 bg-white border border-terracotta text-terracotta font-bold rounded-xl text-sm hover:bg-red-50 transition-colors">
                            Lihat Katalog Menu
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
