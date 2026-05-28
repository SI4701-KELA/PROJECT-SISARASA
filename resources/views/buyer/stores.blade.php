@extends('layouts.buyer')

@section('title', 'Daftar Toko')

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
  <div>
    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Marketplace</p>
    <h1 class="text-3xl font-black text-gray-900 tracking-tight">Daftar Toko</h1>
    <p class="text-sm text-gray-500 mt-1">Temukan berbagai mitra UMKM Sisa Rasa terpercaya di sekitar Anda.</p>
  </div>
  <div class="flex items-center gap-2 bg-white border border-gray-200 px-4 py-2.5 rounded-2xl shadow-sm">
    <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
    <span class="text-sm font-bold text-gray-700">{{ $sellers->count() }} Toko</span>
  </div>
</div>

{{-- Flash Message --}}
@if(session('success'))
<div class="mb-6 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-medium flex items-center gap-2">
  <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
  {{ session('success') }}
</div>
@endif

{{-- Empty State --}}
@if($sellers->count() == 0)
<div class="bg-white border border-gray-100 rounded-3xl p-20 text-center shadow-sm">
  <div class="w-20 h-20 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-5">
    <svg class="w-10 h-10 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
    </svg>
  </div>
  <h3 class="text-xl font-black text-gray-900 mb-2">Belum Ada Toko Terdaftar</h3>
  <p class="text-gray-500 text-sm max-w-sm mx-auto">Saat ini belum ada mitra UMKM yang aktif. Silakan kembali lagi nanti.</p>
</div>

@else
{{-- Store Grid --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
  @foreach($sellers as $seller)
  <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden border border-gray-100 flex flex-col group relative">

    {{-- Favorite Toggle --}}
    <form method="POST" action="{{ route('buyer.favorite.toggle') }}" class="absolute top-4 right-4 z-10">
      @csrf
      <input type="hidden" name="seller_id" value="{{ $seller->id }}">
      <button type="submit"
        class="w-9 h-9 rounded-full flex items-center justify-center transition-all shadow-md hover:scale-110 active:scale-95 {{ in_array($seller->id, $userFavorites ?? []) ? 'bg-red-50' : 'bg-white/90 backdrop-blur-sm hover:bg-red-50' }}"
        title="{{ in_array($seller->id, $userFavorites ?? []) ? 'Hapus dari Favorit' : 'Tambahkan ke Favorit' }}">
        @if(in_array($seller->id, $userFavorites ?? []))
          <svg class="w-4.5 h-4.5 text-red-500" fill="currentColor" viewBox="0 0 24 24"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/></svg>
        @else
          <svg class="w-4.5 h-4.5 text-gray-400 group-hover:text-red-400 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
        @endif
      </button>
    </form>

    {{-- Card Body --}}
    <div class="p-5 flex-grow">
      <div class="flex items-start gap-4">
        {{-- Store Photo --}}
        @if($seller->store_photo)
          <img src="{{ asset('storage/' . $seller->store_photo) }}"
               alt="{{ $seller->store_name }}"
               class="w-14 h-14 rounded-xl object-cover shadow-sm ring-1 ring-gray-100 shrink-0 group-hover:scale-105 transition-transform duration-300">
        @else
          <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-[#2aab7f] to-[#1d8a63] flex items-center justify-center text-white text-xl font-black shadow-sm shrink-0 group-hover:scale-105 transition-transform duration-300">
            {{ strtoupper(substr($seller->store_name ?? 'U', 0, 1)) }}
          </div>
        @endif

        {{-- Store Info --}}
        <div class="flex-1 min-w-0 pr-8">
          <h2 class="text-base font-black text-gray-900 mb-0.5 truncate group-hover:text-[#2aab7f] transition-colors">
            {{ $seller->store_name ?? 'Nama Toko Tidak Tersedia' }}
          </h2>
          @if($seller->reviews_count > 0)
            <div class="flex items-center gap-1 mb-1.5 text-[10px] font-black text-amber-600 bg-amber-50 border border-amber-100 px-2 py-0.5 rounded-lg w-fit">
              <span>★ {{ number_format($seller->reviews_avg_rating, 1) }}</span>
              <span class="text-gray-400 font-semibold">({{ $seller->reviews_count }} Ulasan)</span>
            </div>
          @endif
          <p class="text-xs text-gray-500 line-clamp-2 flex items-start gap-1">
            <svg class="w-3.5 h-3.5 text-gray-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span>{{ $seller->address ?? 'Alamat tidak tersedia' }}</span>
          </p>
        </div>
      </div>

      {{-- Badges --}}
      <div class="mt-4 flex flex-wrap gap-2">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-orange-50 text-orange-700 border border-orange-100">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          {{ $seller->open_time ? date('H:i', strtotime($seller->open_time)) : '--:--' }} – {{ $seller->close_time ? date('H:i', strtotime($seller->close_time)) : '--:--' }}
        </span>
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
          {{ $seller->products_count }} Menu
        </span>
      </div>
    </div>

    {{-- Action Button --}}
    <div class="px-5 pb-5">
      <a href="{{ route('buyer.store.show', $seller->id) }}"
         class="flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-[#2aab7f] hover:bg-[#239970] text-white font-bold text-sm rounded-xl transition-all duration-300 shadow-sm hover:shadow-md">
        Kunjungi Toko
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      </a>
    </div>
  </div>
  @endforeach
</div>
@endif

@endsection
