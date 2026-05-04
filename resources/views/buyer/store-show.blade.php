@extends('layouts.buyer')

@section('title', $seller->store_name ?? 'Detail Toko')

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- Back Button --}}
    <a href="{{ url()->previous() }}"
       class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-500 hover:text-gray-800 mb-6 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali
    </a>

    {{-- Store Hero Card --}}
    <div class="bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-sm mb-6">

        {{-- Cover / Header --}}
        <div class="relative bg-gradient-to-br from-[#2aab7f] to-[#1d8a63] px-6 py-8">

            {{-- Store Photo --}}
            <div class="flex items-center gap-5">
                @if($seller->store_photo)
                    <img src="{{ asset('storage/' . $seller->store_photo) }}"
                         alt="{{ $seller->store_name }}"
                         class="w-20 h-20 rounded-2xl object-cover border-4 border-white/30 shadow-lg shrink-0">
                @else
                    <div class="w-20 h-20 rounded-2xl bg-white/20 border-4 border-white/30 flex items-center justify-center text-white font-black text-3xl shadow-lg shrink-0">
                        {{ strtoupper(substr($seller->store_name ?? 'T', 0, 1)) }}
                    </div>
                @endif

                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl font-black text-white leading-tight truncate">
                        {{ $seller->store_name ?? 'Nama Toko' }}
                    </h1>
                    <span class="mt-1 inline-flex items-center gap-1 px-2.5 py-1 bg-white/20 rounded-full text-xs font-bold text-white">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Terverifikasi
                    </span>
                </div>
            </div>
        </div>

        {{-- Store Info Body --}}
        <div class="px-6 py-5 space-y-4">

            {{-- Description --}}
            @if($seller->description ?? false)
                <div>
                    <p class="text-xs font-bold text-gray-400 tracking-widest uppercase mb-1.5">Tentang Toko</p>
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $seller->description }}</p>
                </div>
            @else
                <div>
                    <p class="text-xs font-bold text-gray-400 tracking-widest uppercase mb-1.5">Tentang Toko</p>
                    <p class="text-sm text-gray-400 italic">Belum ada deskripsi untuk toko ini.</p>
                </div>
            @endif

            <hr class="border-gray-100">

            {{-- Address --}}
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl bg-orange-50 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 tracking-widest uppercase mb-0.5">Alamat</p>
                    <p class="text-sm text-gray-700 font-medium">{{ $seller->address ?? 'Alamat belum tersedia' }}</p>
                </div>
            </div>

            {{-- Jam Buka --}}
            @if($seller->open_time || $seller->close_time)
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl bg-blue-50 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 tracking-widest uppercase mb-0.5">Jam Operasional</p>
                    <p class="text-sm text-gray-700 font-medium">
                        {{ $seller->open_time ? date('H:i', strtotime($seller->open_time)) : '--:--' }}
                        &ndash;
                        {{ $seller->close_time ? date('H:i', strtotime($seller->close_time)) : '--:--' }}
                    </p>
                </div>
            </div>
            @endif

            {{-- Discount Time --}}
            @if($seller->discount_time)
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl bg-red-50 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-[#c04b36]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 tracking-widest uppercase mb-0.5">Mulai Diskon Harga Sisa</p>
                    <p class="text-sm text-[#c04b36] font-bold">
                        {{ date('H:i', strtotime($seller->discount_time)) }} WIB
                    </p>
                </div>
            </div>
            @endif

        </div>
    </div>

    {{-- Products Section --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Section Header --}}
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-gray-900">Katalog Produk</h2>
                <p class="text-xs text-gray-400 mt-0.5">{{ $seller->products->count() }} produk tersedia</p>
            </div>
            <span class="px-3 py-1 bg-[#2aab7f]/10 text-[#2aab7f] text-xs font-bold rounded-full">
                {{ $seller->products->count() }} Menu
            </span>
        </div>

        @if($seller->products->isEmpty())
            <div class="py-16 text-center">
                <div class="w-14 h-14 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                </div>
                <p class="text-sm text-gray-400 font-medium">Belum ada produk di toko ini.</p>
            </div>
        @else
            <div class="divide-y divide-gray-50">
                @foreach($seller->products as $product)
                @php
                    $activeDiscount = $product->discounts->where('is_active', true)->first();
                @endphp
                <div class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 transition-colors">

                    {{-- Product Image --}}
                    <div class="w-16 h-16 rounded-xl overflow-hidden bg-gray-100 shrink-0">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}"
                                 alt="{{ $product->name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif
                    </div>

                    {{-- Product Info --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-800 truncate">{{ $product->name }}</p>
                        @if($product->description ?? false)
                            <p class="text-xs text-gray-400 truncate mt-0.5">{{ $product->description }}</p>
                        @endif
                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                            @if($activeDiscount)
                                <span class="text-xs text-gray-400 line-through">
                                    Rp {{ number_format($product->base_price, 0, ',', '.') }}
                                </span>
                                <span class="text-sm font-black text-[#c04b36]">
                                    Rp {{ number_format($activeDiscount->discount_price, 0, ',', '.') }}
                                </span>
                                <span class="text-[10px] font-bold bg-[#c04b36]/10 text-[#c04b36] px-2 py-0.5 rounded-full">
                                    PROMO
                                </span>
                            @else
                                <span class="text-sm font-bold text-gray-700">
                                    Rp {{ number_format($product->base_price, 0, ',', '.') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Stock Badge --}}
                    @if($product->stock ?? false)
                        <span class="shrink-0 text-xs font-semibold text-gray-400 bg-gray-50 border border-gray-100 px-2 py-1 rounded-lg">
                            Stok: {{ $product->stock->quantity ?? 0 }}
                        </span>
                    @endif

                </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
