@extends('layouts.buyer')

@section('title', 'Keranjang Belanja')

@section('content')
<div class="max-w-5xl mx-auto" x-data="cartManager()" x-init="init()">
    {{-- Page Header --}}
    <div class="flex items-center gap-4 mb-8">
        <div class="w-12 h-12 rounded-2xl bg-red-50 flex items-center justify-center text-terracotta shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Keranjang Belanja</h1>
            <p class="text-sm text-gray-500 font-medium mt-1">Periksa pesanan Anda sebelum melanjutkan ke checkout.</p>
        </div>
    </div>

    @if($cartItems->isEmpty())
        {{-- Empty State --}}
        <div id="empty-cart-state" class="text-center py-24 bg-white rounded-[32px] border border-gray-100 shadow-sm">
            <div class="w-24 h-24 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-terracotta/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Keranjang Anda Kosong</h3>
            <p class="text-gray-500 font-medium mb-8 max-w-sm mx-auto">Belum ada makanan di keranjang Anda. Yuk, cari makanan favorit dari UMKM mitra kami!</p>
            <a href="{{ route('buyer.menu') }}" id="btn-cari-makanan"
               class="inline-flex items-center gap-2 px-8 py-3.5 bg-[#c04b36] text-white font-bold text-sm rounded-2xl shadow-lg hover:shadow-xl hover:bg-[#a33d2b] transition-all duration-300 transform hover:-translate-y-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Cari Makanan Lainnya
            </a>
        </div>
    @else
        <div class="space-y-4" id="cart-items-list">
            @foreach($cartItems as $item)
            <div class="bg-white rounded-[24px] border border-gray-100 shadow-sm p-5 flex items-center gap-5 hover:shadow-md transition-all duration-300 cart-item-row" 
                 id="cart-item-{{ $item->id }}" 
                 x-show="!removedItems.includes({{ $item->id }})">
                
                {{-- Product Image --}}
                <div class="w-20 h-20 rounded-2xl bg-gray-100 overflow-hidden shrink-0">
                    @if($item->product->image)
                        <img src="{{ Storage::url($item->product->image) }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    @endif
                </div>

                {{-- Product Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-start gap-2 mb-1">
                        <h4 class="text-base font-bold text-gray-900 truncate">{{ $item->product->name }}</h4>
                        @if($item->is_surplus)
                            <span class="shrink-0 px-2 py-0.5 bg-gradient-to-r from-red-500 to-orange-400 text-white text-[10px] font-bold uppercase tracking-wider rounded-lg shadow-sm badge-surplus">
                                Promo Sisa Rasa
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 font-medium">{{ $item->product->seller->store_name ?? 'Toko' }} · {{ $item->product->category->name ?? '' }}</p>

                    {{-- Price --}}
                    <div class="mt-2">
                        @if($item->is_surplus)
                            <span class="text-gray-400 text-xs font-medium line-through mr-1">Rp {{ number_format($item->product->base_price, 0, ',', '.') }}</span>
                            <span class="text-[#c04b36] font-extrabold text-sm item-price">Rp {{ number_format($item->effective_price, 0, ',', '.') }}</span>
                        @else
                            <span class="text-gray-900 font-extrabold text-sm item-price">Rp {{ number_format($item->effective_price, 0, ',', '.') }}</span>
                        @endif
                    </div>
                </div>

                {{-- Qty Stepper --}}
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button"
                            class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-50 hover:border-gray-300 transition-all disabled:opacity-30 disabled:cursor-not-allowed btn-decrement"
                            :disabled="quantities[{{ $item->id }}] <= 1"
                            @click="updateQty({{ $item->id }}, quantities[{{ $item->id }}] - 1, {{ $item->effective_price }})"
                            aria-label="Kurangi jumlah">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                    </button>
                    <span class="w-10 text-center font-bold text-gray-900 text-sm qty-display" id="qty-{{ $item->id }}" x-text="quantities[{{ $item->id }}]">{{ $item->qty }}</span>
                    <button type="button"
                            class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-50 hover:border-gray-300 transition-all disabled:opacity-30 disabled:cursor-not-allowed btn-increment"
                            :disabled="quantities[{{ $item->id }}] >= maxStocks[{{ $item->id }}]"
                            @click="updateQty({{ $item->id }}, quantities[{{ $item->id }}] + 1, {{ $item->effective_price }})"
                            aria-label="Tambah jumlah">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    </button>
                </div>

                {{-- Subtotal --}}
                <div class="text-right shrink-0 w-32">
                    <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Subtotal</p>
                    <p class="text-base font-extrabold text-gray-900 subtotal-display" id="subtotal-{{ $item->id }}" x-text="formatRupiah(subtotals[{{ $item->id }}])">
                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                    </p>
                </div>

                {{-- Delete Button --}}
                <button type="button"
                        class="w-10 h-10 rounded-xl flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 transition-all shrink-0 btn-delete"
                        @click="removeItem({{ $item->id }}, {{ $item->subtotal }})"
                        aria-label="Hapus item">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
            @endforeach
        </div>

        {{-- Total Tagihan --}}
        <div class="mt-8 bg-white rounded-[24px] border border-gray-100 shadow-sm p-6" id="total-section">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-semibold">Total Tagihan</p>
                    <p class="text-[10px] text-gray-400 font-medium mt-0.5" x-text="Object.keys(quantities).length - removedItems.length + ' item(s)'"></p>
                </div>
                <p class="text-2xl font-black text-gray-900" id="grand-total" x-text="formatRupiah(grandTotal)">
                    Rp {{ number_format($grandTotal, 0, ',', '.') }}
                </p>
            </div>
            <div class="mt-5 flex gap-3 justify-end">
                <a href="{{ route('buyer.menu') }}" class="px-6 py-3 border border-gray-200 text-gray-600 font-bold text-sm rounded-2xl hover:bg-gray-50 transition-all">
                    Lanjut Belanja
                </a>
                <a href="{{ route('buyer.checkout') }}" class="px-8 py-3 bg-[#c04b36] text-white font-bold text-sm rounded-2xl shadow-lg hover:shadow-xl hover:bg-[#a33d2b] transition-all duration-300 transform hover:-translate-y-0.5">
                    Checkout
                </a>
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.cart-item-row { animation: fadeSlideIn 0.3s ease-out; }
@keyframes fadeSlideIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endpush

@push('scripts')
<script>
function cartManager() {
    return {
        quantities: {!! json_encode($cartItems->pluck('qty', 'id')) !!},
        subtotals: {!! json_encode($cartItems->pluck('subtotal', 'id')) !!},
        maxStocks: {!! json_encode($cartItems->pluck('max_qty', 'id')) !!},
        prices: {!! json_encode($cartItems->pluck('effective_price', 'id')) !!},
        grandTotal: {{ $grandTotal }},
        removedItems: [],
        stockError: '',

        init() {},

        formatRupiah(val) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
        },

        async updateQty(itemId, newQty, price) {
            if (newQty < 1 || newQty > this.maxStocks[itemId]) return;

            const oldQty = this.quantities[itemId];
            const oldSubtotal = this.subtotals[itemId];

            this.quantities[itemId] = newQty;
            this.subtotals[itemId] = price * newQty;
            this.grandTotal = this.grandTotal - oldSubtotal + this.subtotals[itemId];

            try {
                const res = await fetch(`/buyer/cart/${itemId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ qty: newQty })
                });

                if (!res.ok) {
                    // Rollback
                    this.quantities[itemId] = oldQty;
                    this.subtotals[itemId] = oldSubtotal;
                    this.grandTotal = this.grandTotal - this.subtotals[itemId] + oldSubtotal;
                    const data = await res.json();
                    alert(data.error || 'Gagal mengubah jumlah.');
                }
            } catch (e) {
                this.quantities[itemId] = oldQty;
                this.subtotals[itemId] = oldSubtotal;
                this.grandTotal = this.grandTotal - this.subtotals[itemId] + oldSubtotal;
            }
        },

        async removeItem(itemId) {
            const sub = this.subtotals[itemId];
            this.removedItems.push(itemId);
            this.grandTotal -= sub;

            try {
                await fetch(`/buyer/cart/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
            } catch (e) {
                // Rollback on error
                this.removedItems = this.removedItems.filter(id => id !== itemId);
                this.grandTotal += sub;
            }

            // If all removed, reload for empty state
            const remaining = Object.keys(this.quantities).length - this.removedItems.length;
            if (remaining <= 0) {
                window.location.reload();
            }
        }
    };
}
</script>
@endpush
