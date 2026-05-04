@extends('layouts.buyer')

@section('title', 'Toko Favorit')

@section('content')
<div class="max-w-4xl mx-auto">

    {{-- Page Header --}}
    <div class="flex items-center gap-3 mb-2">
        <div class="w-9 h-9 rounded-full bg-red-50 flex items-center justify-center">
            <svg class="w-5 h-5 text-[#c04b36]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
        </div>
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Toko Favorit</h1>
            <p class="text-sm text-gray-400 font-medium">Toko mitra UMKM yang telah Anda simpan.</p>
        </div>
    </div>

    {{-- Flash Message --}}
    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-medium flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Summary Banner --}}
    <div class="mb-6 mt-5 border border-[#c04b36]/30 rounded-xl px-5 py-4 flex items-center gap-3 bg-white">
        <div class="w-8 h-8 rounded-full bg-red-50 flex items-center justify-center shrink-0">
            <svg class="w-4 h-4 text-[#c04b36]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
        </div>
        <p class="text-sm text-gray-600">
            Daftar Toko Favorit Anda<br>
            <span class="font-semibold text-gray-800">
                Menampilkan
                <span class="text-[#c04b36] font-bold">{{ $sellers->count() }} Toko</span>
                yang telah Anda simpan.
            </span>
        </p>
    </div>

    {{-- Empty State --}}
    @if($sellers->count() == 0)
        <div class="bg-white border border-gray-100 rounded-2xl p-16 text-center shadow-sm">
            <div class="w-20 h-20 mx-auto mb-5 bg-red-50 rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-red-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Belum Ada Toko Tersimpan</h3>
            <p class="text-gray-400 text-sm mb-6 max-w-xs mx-auto">Jelajahi UMKM dan ketuk ikon hati untuk menyimpan toko favorit kamu.</p>
            <a href="{{ route('buyer.stores') }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#c04b36] hover:bg-[#a83c29] text-white font-bold rounded-xl text-sm transition-colors">
                Jelajahi Semua UMKM
            </a>
        </div>

    @else
        {{-- Store Cards --}}
        <div class="flex flex-col gap-5">
            @foreach($sellers as $seller)
            @php
                $visibleProducts = $seller->products ? $seller->products->take(3) : collect();
                $remainingCount  = $seller->products ? max(0, $seller->products->count() - 3) : 0;
            @endphp
            <div class="bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-sm">

                {{-- Card Header: store name + location --}}
                <div class="px-5 py-4 bg-[#2aab7f]">
                    <h2 class="text-white font-bold text-base leading-tight">{{ $seller->store_name ?? 'Nama Toko' }}</h2>
                    @if($seller->address)
                        <p class="text-white/75 text-xs mt-0.5">{{ Str::limit($seller->address, 50) }}</p>
                    @endif
                </div>

                {{-- Product List --}}
                <div class="px-5 py-4">
                    <p class="text-[10px] font-bold text-gray-400 tracking-widest uppercase mb-3">Katalog UMKM Ini:</p>

                    @if($visibleProducts->isEmpty())
                        <p class="text-sm text-gray-400 italic py-2">Belum ada produk di toko ini.</p>
                    @else
                        <div class="flex flex-col divide-y divide-gray-50">
                            @foreach($visibleProducts as $product)
                            @php
                                $activeDiscount = $product->discount && $product->discount->is_active
                                    ? $product->discount
                                    : null;
                            @endphp
                            <div class="flex items-center gap-3 py-2.5">
                                {{-- Product Image --}}
                                <div class="w-11 h-11 rounded-lg overflow-hidden bg-gray-100 shrink-0">
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}"
                                             alt="{{ $product->name }}"
                                             class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                    @endif
                                </div>

                                {{-- Product Info --}}
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $product->name }}</p>
                                    <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                        @if($activeDiscount)
                                            <span class="text-xs text-gray-400 line-through">
                                                Rp {{ number_format($product->base_price, 0, ',', '.') }}
                                            </span>
                                            <span class="text-xs font-bold text-[#c04b36]">
                                                Rp {{ number_format($activeDiscount->discount_price, 0, ',', '.') }}
                                            </span>
                                            <span class="text-[10px] font-bold bg-[#c04b36]/10 text-[#c04b36] px-1.5 py-0.5 rounded">PROMO</span>
                                        @else
                                            <span class="text-xs font-semibold text-gray-600">
                                                Rp {{ number_format($product->base_price, 0, ',', '.') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        @if($remainingCount > 0)
                            <a href="{{ route('buyer.menu') }}" class="inline-block mt-2 text-xs font-semibold text-[#c04b36] hover:underline">
                                +{{ $remainingCount }} produk lainnya
                            </a>
                        @endif
                    @endif
                </div>

                {{-- Card Footer --}}
                <div class="px-5 pb-4 flex items-center gap-3">
                    {{-- Unfavorite Button --}}
                    <form method="POST" action="{{ route('buyer.favorite.toggle') }}">
                        @csrf
                        <input type="hidden" name="seller_id" value="{{ $seller->id }}">
                        <button type="submit"
                                title="Hapus dari Favorit"
                                class="w-10 h-10 rounded-xl border-2 border-[#c04b36]/30 hover:border-[#c04b36] flex items-center justify-center text-[#c04b36] hover:bg-red-50 transition-all">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/>
                            </svg>
                        </button>
                    </form>

                    {{-- Visit Store Button --}}
                    <a href="{{ route('buyer.store.show', $seller->id) }}"
                       class="flex-1 text-center py-2.5 border-2 border-[#2aab7f] text-[#2aab7f] font-bold text-sm rounded-xl hover:bg-[#2aab7f] hover:text-white transition-all duration-200">
                        Kunjungi Toko
                    </a>
                </div>

            </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
