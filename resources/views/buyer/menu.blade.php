@extends('layouts.buyer')

@section('title', 'Katalog Menu')

@section('content')
<div class="max-w-7xl mx-auto">
    {{-- Page Header --}}
    <div class="flex items-center gap-4 mb-8">
        <div class="w-12 h-12 rounded-2xl bg-red-50 flex items-center justify-center text-terracotta shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </div>
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Katalog Menu Sisa Rasa</h1>
            <p class="text-sm text-gray-500 font-medium mt-1">Temukan makanan berkualitas dengan harga terbaik dari UMKM mitra kami.</p>
        </div>
    </div>

    {{-- Komponen Navigator Kategori --}}
    <div class="flex overflow-x-auto pb-4 mb-8 gap-3 scrollbar-hide">
        <a href="{{ route('buyer.menu') }}"
           class="whitespace-nowrap px-5 py-2.5 rounded-full text-sm font-bold transition-all {{ is_null($categoryId) ? 'bg-[#c04b36] text-white shadow-sm' : 'bg-white border border-gray-100 text-gray-500 hover:border-terracotta hover:text-terracotta' }}">
            Semua Makanan
        </a>
        @foreach($categories as $category)
            <a href="{{ route('buyer.menu', ['category_id' => $category->id]) }}"
               class="whitespace-nowrap px-5 py-2.5 rounded-full text-sm font-bold transition-all {{ $categoryId == $category->id ? 'bg-[#c04b36] text-white shadow-sm' : 'bg-white border border-gray-100 text-gray-500 hover:border-terracotta hover:text-terracotta' }}">
                {{ $category->name }}
            </a>
        @endforeach
    </div>

    {{-- Grid Catalog --}}
    @if($products->isEmpty())
        <div class="text-center py-20 bg-white rounded-[32px] border border-gray-100 shadow-sm">
            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">Tidak ada produk</h3>
            <p class="text-gray-500 font-medium">Belum ada makanan untuk kategori ini.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($products as $product)
                @php
                    $activeDiscount = isset($product->discounts[0]) && $product->discounts[0]->is_active ? $product->discounts[0] : null;
                    $isSurplus = $activeDiscount ? true : false;
                    $currentStock = $isSurplus ? ($product->stock->qty_surplus ?? 0) : ($product->stock->qty_reg ?? 0);
                @endphp
                <div class="bg-white rounded-[24px] overflow-hidden shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 relative group flex flex-col">
                    {{-- Product Image --}}
                    <div class="h-48 w-full bg-gray-100 relative overflow-hidden">
                        @if($product->image)
                            <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        @endif
                        {{-- Category Badge --}}
                        <div class="absolute top-3 left-3">
                            <span class="px-2.5 py-1 bg-white/90 backdrop-blur-sm text-gray-800 text-[10px] font-bold uppercase tracking-wider rounded-lg shadow-sm">
                                {{ $product->category->name ?? 'Uncategorized' }}
                            </span>
                        </div>
                        {{-- Promo Badge --}}
                        @if($activeDiscount)
                            <div class="absolute top-3 right-3">
                                <span class="promo-badge px-2 py-1 bg-red-500 text-white text-[10px] font-bold uppercase tracking-wider rounded-lg shadow-sm">PROMO</span>
                            </div>
                        @endif
                    </div>

                    {{-- Product Info --}}
                    <div class="p-5 flex-1 flex flex-col">
                        <h4 class="text-base font-bold text-gray-900 mb-1 truncate">{{ $product->name }}</h4>
                        <p class="text-xs text-gray-500 mb-3 flex items-center gap-1 font-medium">
                            <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span class="truncate">{{ $product->seller->store_name ?? 'Toko Tidak Diketahui' }}</span>
                        </p>

                        <div class="flex items-end justify-between mb-4">
                            <div class="flex flex-col justify-end">
                                @if($activeDiscount)
                                    <p class="harga-coret text-gray-400 text-xs font-medium line-through">Rp {{ number_format($product->base_price, 0, ',', '.') }}</p>
                                    <p class="text-[#c04b36] font-extrabold text-base">Rp {{ number_format($activeDiscount->discount_price, 0, ',', '.') }}</p>
                                @else
                                    <p class="text-gray-900 font-extrabold text-base">Rp {{ number_format($product->base_price, 0, ',', '.') }}</p>
                                @endif
                            </div>
                            <div class="text-right">
                                <span class="px-2.5 py-1 bg-orange-50 border border-orange-100 text-orange-600 text-xs font-bold rounded-full">
                                    {{ $currentStock }} Porsi
                                </span>
                            </div>
                        </div>

                        {{-- Add to Cart --}}
                        @if($currentStock > 0)
                        <div x-data="{ 
                            qty: 0, 
                            maxQty: {{ $currentStock }}, 
                            loading: false,
                            async addToCart() {
                                if(this.qty > 0 && !this.loading) {
                                    this.loading = true;
                                    try {
                                        const res = await fetch('{{ route('buyer.cart.store') }}', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'Accept': 'application/json'
                                            },
                                            body: JSON.stringify({
                                                product_id: {{ $product->id }},
                                                qty: this.qty,
                                                is_surplus: {{ $isSurplus ? 'true' : 'false' }}
                                            })
                                        });
                                        const data = await res.json();
                                        if (res.ok && data.success) {
                                            showCartToast(data.message || 'Berhasil menambahkan ke keranjang!');
                                            const badge = document.querySelector('.cart-badge-count');
                                            if (badge) badge.textContent = data.cart_count;
                                            this.maxQty = Math.max(0, this.maxQty - this.qty);
                                            this.qty = 0;
                                        } else {
                                            showCartToast(data.error || 'Gagal menambahkan ke keranjang.', true);
                                        }
                                    } catch (e) {
                                        showCartToast('Terjadi kesalahan jaringan.', true);
                                    } finally {
                                        this.loading = false;
                                    }
                                }
                            }
                        }" class="mt-auto">
                            <div class="flex items-center gap-2">
                                <div class="flex items-center border border-gray-200 rounded-xl overflow-hidden bg-white shadow-sm flex-1">
                                    <button @click="if(qty > 0) qty--" type="button" class="w-9 h-9 flex items-center justify-center text-gray-500 hover:bg-gray-50 hover:text-[#c04b36] transition-colors" :class="{ 'opacity-40 cursor-not-allowed': qty == 0 }">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                                    </button>
                                    <div class="flex-1 h-9 flex items-center justify-center border-x border-gray-100 bg-gray-50">
                                        <span x-text="qty" class="text-sm font-bold text-gray-800"></span>
                                    </div>
                                    <button @click="if(qty < maxQty) qty++" type="button" class="w-9 h-9 flex items-center justify-center text-gray-500 hover:bg-gray-50 hover:text-[#c04b36] transition-colors" :class="{ 'opacity-40 cursor-not-allowed': qty >= maxQty }">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    </button>
                                </div>
                                <button @click="addToCart()" x-show="qty > 0" x-transition.scale.origin.right type="button"
                                        class="h-9 px-3 flex items-center gap-1.5 bg-[#c04b36] text-white text-xs font-bold rounded-xl hover:bg-[#a33d2b] transition-all shadow-sm disabled:opacity-50 shrink-0"
                                        :disabled="loading">
                                    <template x-if="!loading">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    </template>
                                    <template x-if="loading">
                                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    </template>
                                    <span x-text="loading ? '...' : '+'"></span>
                                </button>
                            </div>
                        </div>
                        @else
                        <div class="mt-auto pt-2">
                            <span class="inline-block w-full text-center py-2 bg-gray-100 text-gray-400 text-xs font-bold rounded-xl">Stok Habis</span>
                        </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-8">
            {{ $products->appends(['category_id' => $categoryId])->links() }}
        </div>
    @endif
</div>
@endsection
