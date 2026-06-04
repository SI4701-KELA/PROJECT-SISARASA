@extends('layouts.buyer')

@section('title', 'Checkout')

@section('content')
<div class="max-w-5xl mx-auto" x-data="checkoutManager()">
    {{-- Page Header --}}
    <div class="flex items-center gap-4 mb-8">
        <div class="w-12 h-12 rounded-2xl bg-red-50 flex items-center justify-center text-terracotta shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Checkout</h1>
            <p class="text-sm text-gray-500 font-medium mt-1">Tinjau pesanan dan pilih metode pembayaran.</p>
        </div>
    </div>

    {{-- Error Messages --}}
    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-5 py-3 rounded-2xl shadow-sm font-medium text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('buyer.checkout.store') }}" method="POST" enctype="multipart/form-data" id="checkout-form">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left: Order Summary --}}
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-white rounded-[24px] border border-gray-100 shadow-sm p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Ringkasan Pesanan</h2>
                    <p class="text-xs text-gray-400 font-semibold mb-4">Toko: {{ $seller->store_name ?? 'Toko' }}</p>

                    <div class="space-y-3">
                        @foreach($cartItems as $item)
                        <div class="flex items-center gap-4 py-3 border-b border-gray-50 last:border-0">
                            {{-- Image --}}
                            <div class="w-14 h-14 rounded-xl bg-gray-100 overflow-hidden shrink-0">
                                @if($item->product->image)
                                    <img src="{{ Storage::url($item->product->image) }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-300">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif
                            </div>

                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-bold text-gray-900 truncate">{{ $item->product->name }}</h4>
                                    @if($item->is_surplus)
                                        <span class="shrink-0 px-2 py-0.5 bg-gradient-to-r from-red-500 to-orange-400 text-white text-[9px] font-bold uppercase tracking-wider rounded-md badge-surplus">
                                            Promo Sisa Rasa
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-400 font-medium mt-0.5">{{ $item->qty }} × Rp {{ number_format($item->effective_price, 0, ',', '.') }}</p>
                            </div>

                            {{-- Subtotal --}}
                            <div class="text-right shrink-0">
                                <p class="text-sm font-extrabold text-gray-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Promo/Voucher Section --}}
                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <label for="promo_code_input" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Kode Voucher / Promo</label>
                        <div class="flex gap-3">
                            <input type="text" id="promo_code_input" x-model="promoInput" :disabled="voucherApplied"
                                   placeholder="Contoh: SISARASABARU"
                                   class="flex-1 bg-gray-50 border border-gray-200 focus:bg-white focus:border-[#c04b36] focus:ring-1 focus:ring-[#c04b36] rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 font-semibold transition-all">
                            <button type="button" @click="applyVoucher" :disabled="voucherApplied || !promoInput"
                                    class="px-5 py-2.5 bg-[#c04b36] hover:bg-[#a33d2b] disabled:bg-gray-200 disabled:text-gray-400 text-white font-bold text-xs rounded-xl transition-all shadow-md shrink-0 flex items-center justify-center gap-2">
                                <span x-show="checkingVoucher" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                <span x-text="voucherApplied ? 'Terpasang' : 'Gunakan'"></span>
                            </button>
                            <button type="button" x-show="voucherApplied" @click="removeVoucher"
                                    class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold text-xs rounded-xl transition-all shrink-0">
                                Batal
                            </button>
                        </div>
                        @if(!$vouchers->isEmpty())
                        <div class="mt-3" x-show="!voucherApplied">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Voucher Tersedia untuk Toko Ini:</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($vouchers as $voucher)
                                    @php
                                        $label = $voucher->type === 'percent' ? $voucher->value . '%' : 'Rp ' . number_format($voucher->value, 0, ',', '.');
                                    @endphp
                                    <button type="button" @click="promoInput = '{{ $voucher->code }}'; applyVoucher()"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-orange-50 hover:bg-orange-100 border border-dashed border-orange-200 rounded-lg text-xs font-semibold text-orange-700 transition-all hover:scale-[1.02] focus:outline-none">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                        <span class="font-mono font-bold">{{ $voucher->code }}</span>
                                        <span class="text-[10px] bg-white px-1.5 py-0.5 rounded border border-orange-100 font-bold text-orange-600">Potongan {{ $label }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        <div class="mt-2 text-xs font-bold" :class="voucherSuccess ? 'text-green-600' : 'text-red-500'" x-show="voucherMessage" x-cloak>
                            <span x-text="voucherMessage"></span>
                        </div>
                        <input type="hidden" name="promo_code" :value="appliedVoucherCode">
                    </div>

                    {{-- Discount Breakdown (visible only when voucher applied) --}}
                    <div class="mt-4 pt-3 border-t border-gray-100/50 flex items-center justify-between text-sm" x-show="voucherApplied" x-cloak>
                        <p class="font-medium text-gray-500">Potongan Voucher (<span class="font-bold text-gray-800" x-text="appliedVoucherCode"></span>)</p>
                        <p class="font-bold text-green-600">-Rp <span x-text="formatRupiah(discountAmount)"></span></p>
                    </div>

                    {{-- Total --}}
                    <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
                        <p class="text-sm font-bold text-gray-500">Total Tagihan</p>
                        <p class="text-xl font-black text-gray-900" id="checkout-total" x-text="'Rp ' + formatRupiah(finalTotal)"></p>
                    </div>
                </div>
            </div>

            {{-- Right: Payment Method --}}
            <div class="space-y-4">
                <div class="bg-white rounded-[24px] border border-gray-100 shadow-sm p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Metode Pembayaran</h2>

                    {{-- Cash Option --}}
                    <label class="flex items-center gap-4 p-4 rounded-2xl border-2 cursor-pointer transition-all duration-200 mb-3"
                           :class="paymentMethod === 'cash' ? 'border-[#c04b36] bg-red-50/50' : 'border-gray-100 hover:border-gray-200'"
                           id="payment-option-cash">
                        <input type="radio" name="payment_method" value="cash"
                               x-model="paymentMethod"
                               class="w-5 h-5 text-[#c04b36] border-gray-300 focus:ring-[#c04b36]">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-gray-900">Cash</p>
                            <p class="text-xs text-gray-400 font-medium">Bayar langsung saat pengambilan</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                    </label>

                    {{-- QRIS Option --}}
                    <label class="flex items-center gap-4 p-4 rounded-2xl border-2 cursor-pointer transition-all duration-200"
                           :class="paymentMethod === 'qris' ? 'border-[#c04b36] bg-red-50/50' : 'border-gray-100 hover:border-gray-200'"
                           id="payment-option-qris">
                        <input type="radio" name="payment_method" value="qris"
                               x-model="paymentMethod"
                               class="w-5 h-5 text-[#c04b36] border-gray-300 focus:ring-[#c04b36]">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-gray-900">QRIS</p>
                            <p class="text-xs text-gray-400 font-medium">Scan & bayar via e-wallet</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        </div>
                    </label>

                    {{-- QRIS Detail Panel --}}
                    <div x-show="paymentMethod === 'qris'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="mt-4" id="qris-panel">

                        @if($seller->qris_image)
                            {{-- QRIS Barcode --}}
                            <div class="bg-gray-50 rounded-2xl p-4 text-center mb-4" id="qris-barcode-container">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">QRIS {{ $seller->store_name }}</p>
                                <div class="bg-white rounded-xl p-3 inline-block shadow-sm border border-gray-100">
                                    <img src="{{ Storage::url($seller->qris_image) }}" alt="QRIS {{ $seller->store_name }}" class="max-w-[200px] mx-auto" id="qris-image">
                                </div>
                                <p class="text-[10px] text-gray-400 mt-3 font-medium">Scan barcode di atas, lalu unggah bukti transfer</p>
                            </div>

                            {{-- Upload Bukti Transfer --}}
                            <div class="space-y-3" id="upload-proof-section">
                                <label class="block text-sm font-bold text-gray-700">Unggah Bukti Transfer</label>
                                <div class="relative">
                                    <input type="file" name="payment_proof" id="payment_proof" accept="image/jpeg,image/png,image/jpg"
                                           class="w-full text-sm text-gray-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-red-50 file:text-[#c04b36] hover:file:bg-red-100 cursor-pointer transition-colors border border-gray-200 rounded-xl"
                                           @change="proofUploaded = $event.target.files.length > 0">
                                </div>
                                <p class="text-[10px] text-gray-400 font-medium">Format: JPG/PNG. Maks: 2MB.</p>
                            </div>
                        @else
                            {{-- Toko belum punya QRIS --}}
                            <div class="bg-orange-50 border border-orange-200 rounded-2xl p-4 text-center" id="qris-not-available">
                                <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.27 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                </div>
                                <p class="text-sm font-bold text-orange-700 mb-1">QRIS Belum Tersedia</p>
                                <p class="text-xs text-orange-600 font-medium">Toko ini belum mengatur pembayaran QRIS. Silakan gunakan metode Cash.</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Submit Button --}}
                <button type="submit" id="btn-buat-pesanan"
                        class="w-full py-4 bg-[#c04b36] text-white font-bold text-sm rounded-2xl shadow-lg hover:shadow-xl hover:bg-[#a33d2b] transition-all duration-300 transform hover:-translate-y-0.5 disabled:opacity-40 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none"
                        :disabled="!canSubmit">
                    <span class="flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Buat Pesanan
                    </span>
                </button>

                <a href="{{ route('buyer.cart') }}" class="block text-center text-sm font-bold text-gray-500 hover:text-gray-700 transition-colors">
                    ← Kembali ke Keranjang
                </a>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function checkoutManager() {
    return {
        paymentMethod: '',
        proofUploaded: false,
        
        // Voucher state
        promoInput: '',
        checkingVoucher: false,
        voucherApplied: false,
        voucherSuccess: false,
        voucherMessage: '',
        appliedVoucherCode: '',
        discountAmount: 0,
        initialTotal: {{ $grandTotal }},

        get finalTotal() {
            return Math.max(0, this.initialTotal - this.discountAmount);
        },

        get canSubmit() {
            if (!this.paymentMethod) return false;
            if (this.paymentMethod === 'cash') return true;
            if (this.paymentMethod === 'qris') {
                const qrisAvailable = document.getElementById('qris-barcode-container') !== null;
                if (!qrisAvailable) return false;
                return this.proofUploaded;
            }
            return false;
        },

        applyVoucher() {
            if (!this.promoInput) return;
            this.checkingVoucher = true;
            this.voucherMessage = '';

            fetch('{{ route('buyer.checkout.check-voucher') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    code: this.promoInput
                })
            })
            .then(res => res.json().then(data => ({ status: res.status, data })))
            .then(({ status, data }) => {
                this.checkingVoucher = false;
                if (status === 200 && data.success) {
                    this.voucherApplied = true;
                    this.voucherSuccess = true;
                    this.voucherMessage = data.message;
                    this.appliedVoucherCode = this.promoInput.toUpperCase().trim();
                    this.discountAmount = data.discount;
                } else {
                    this.voucherApplied = false;
                    this.voucherSuccess = false;
                    this.voucherMessage = data.message || 'Gagal menerapkan voucher.';
                    this.discountAmount = 0;
                    this.appliedVoucherCode = '';
                }
            })
            .catch(err => {
                this.checkingVoucher = false;
                this.voucherApplied = false;
                this.voucherSuccess = false;
                this.voucherMessage = 'Terjadi kesalahan koneksi.';
                this.discountAmount = 0;
                this.appliedVoucherCode = '';
            });
        },

        removeVoucher() {
            this.promoInput = '';
            this.voucherApplied = false;
            this.voucherSuccess = false;
            this.voucherMessage = '';
            this.appliedVoucherCode = '';
            this.discountAmount = 0;
        },

        formatRupiah(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }
    };
}
</script>
@endpush
