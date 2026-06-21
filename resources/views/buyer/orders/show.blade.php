@extends('layouts.buyer')

@section('title', 'Detail Invoice #' . $order->id)

@push('styles')
<style>
    [x-cloak] { display: none !important; }

    @media print {
        body * {
            visibility: hidden;
        }
        #print-area, #print-area * {
            visibility: visible;
        }
        #print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .no-print {
            display: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="max-w-3xl mx-auto py-4">
    {{-- Status Banners --}}
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl shadow-sm font-medium text-sm flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if($order->status === 'hangus')
    <div class="mb-6 bg-orange-50 border border-orange-300 rounded-xl p-4 text-center shadow-sm">
        <h3 class="text-orange-600 font-bold text-lg">Pesanan Hangus</h3>
        <p class="text-orange-500 text-sm mt-1">Pesanan tidak diambil dalam batas waktu yang ditentukan.</p>
    </div>
    @elseif($order->status === 'dibatalkan')
    <div class="mb-6 bg-white border border-teal-500 rounded-xl p-4 text-center shadow-sm">
        <h3 class="text-teal-600 font-bold text-lg">Pesanan Berhasil di batalkan</h3>
    </div>
    @elseif($order->status === 'diproses')
    <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl shadow-sm text-sm text-center">
        Pesanan sedang disiapkan. Estimasi waktu penyiapan: <span class="font-bold">15-20 Menit</span>
    </div>
    @elseif($order->status === 'siap_diambil' && $order->pickup_deadline)
        @php
            $deadlineIso = $order->pickup_deadline->timezone('Asia/Jakarta')->toIso8601String();
            $deadlineTime = $order->pickup_deadline->timezone('Asia/Jakarta')->format('H:i');
        @endphp
        <div x-data="{
            deadline: new Date('{{ $deadlineIso }}').getTime(),
            now: new Date().getTime(),
            get isExpired() {
                return this.now >= this.deadline;
            },
            get timeLeft() {
                return Math.max(0, Math.floor((this.deadline - this.now) / 1000));
            },
            init() {
                setInterval(() => {
                    this.now = new Date().getTime();
                }, 1000);
            },
            formatTime() {
                let t = this.timeLeft;
                let h = Math.floor(t / 3600);
                let m = Math.floor((t % 3600) / 60);
                let s = t % 60;
                return [h, m, s].map(v => v.toString().padStart(2, '0')).join(':');
            }
        }" class="text-center">
            <div x-show="isExpired" style="display: none" class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl shadow-sm text-sm font-bold">
                Batas waktu pengambilan telah terlewat.
            </div>
            <div x-show="!isExpired" style="display: none" class="mb-6 bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-xl shadow-sm text-sm">
                Harap ambil pesanan Anda dalam waktu: <span class="font-bold text-red-600 font-mono text-base ml-1" x-text="formatTime()"></span>
                <br><span class="text-xs text-gray-600">(Batas Maksimal: {{ $deadlineTime }} WIB)</span>
            </div>
        </div>
    @endif

    {{-- Back Link & Print Action (no-print) --}}
    <div class="flex items-center justify-between mb-8 no-print">
        <a href="{{ route('buyer.orders.index', ['tab' => 'riwayat']) }}" class="inline-flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-gray-900 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Riwayat
        </a>

        <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 hover:border-gray-900 text-gray-700 hover:text-gray-900 font-bold text-sm rounded-xl transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Cetak Invoice
        </button>
    </div>

    {{-- Invoice Paper Receipt Card --}}
    <div id="print-area" class="bg-white rounded-[32px] border border-gray-100 shadow-sm overflow-hidden relative">
        {{-- Decorative receipt top border --}}
        <div class="h-2 bg-gradient-to-r from-red-500 via-orange-500 to-[#c04b36]"></div>

        <div class="p-8 md:p-12">
            {{-- Brand & Header --}}
            <div class="text-center mb-8">
                <h2 class="text-2xl font-black text-gray-900 tracking-tight">Sisa Rasa</h2>
                <p class="text-xs font-semibold text-gray-400 mt-1 uppercase tracking-[0.2em]">Nota Pembayaran Resmi</p>
                <p class="text-[10px] text-gray-400 mt-1">Penyelamat Makanan Lezat & Ramah Lingkungan</p>
            </div>

            {{-- Dotted Divider --}}
            <div class="border-t-2 border-dashed border-gray-100 my-6"></div>

            {{-- Metadata Invoice --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mb-6">
                <div class="space-y-2">
                    <div class="flex justify-between md:justify-start gap-4">
                        <span class="text-gray-400 font-semibold w-28">No. Transaksi</span>
                        <span class="text-gray-900 font-bold">#{{ $order->id }}</span>
                    </div>
                    <div class="flex justify-between md:justify-start gap-4">
                        <span class="text-gray-400 font-semibold w-28">Waktu Transaksi</span>
                        <span class="text-gray-900 font-medium">{{ $order->created_at->format('d M Y, H:i') }} WIB</span>
                    </div>
                    <div class="flex justify-between md:justify-start gap-4">
                        <span class="text-gray-400 font-semibold w-28">Nama Pembeli</span>
                        <span class="text-gray-900 font-medium">{{ Auth::user()->name }}</span>
                    </div>
                </div>
                <div class="space-y-2 md:text-right md:flex md:flex-col md:items-end md:justify-start">
                    <div class="flex justify-between md:justify-end gap-4 w-full">
                        <span class="text-gray-400 font-semibold md:w-auto">Metode Pembayaran</span>
                        <span class="text-gray-900 font-bold uppercase">{{ $order->payment_method }}</span>
                    </div>
                    <div class="flex justify-between md:justify-end gap-4 w-full items-center">
                        <span class="text-gray-400 font-semibold md:w-auto">Status Pesanan</span>
                        @if($order->status === 'selesai')
                            <span class="px-2.5 py-0.5 bg-green-50 text-green-700 text-xs font-bold rounded border border-green-200 uppercase tracking-wide">Selesai</span>
                        @elseif($order->status === 'dibatalkan')
                            <span class="px-2.5 py-0.5 bg-red-50 text-red-600 text-xs font-bold rounded border border-red-200 uppercase tracking-wide">Dibatalkan</span>
                        @elseif($order->status === 'hangus')
                            <span class="px-2.5 py-0.5 bg-orange-50 text-orange-600 text-xs font-bold rounded border border-orange-200 uppercase tracking-wide">Hangus</span>
                        @else
                            <span class="px-2.5 py-0.5 bg-gray-50 text-gray-600 text-xs font-bold rounded border border-gray-200 uppercase tracking-wide">{{ $order->status }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Dotted Divider --}}
            <div class="border-t-2 border-dashed border-gray-100 my-6"></div>

            @if($order->status === 'siap_diambil' && $order->pickup_code)
                {{-- QR Code & Pickup Code Section --}}
                <div class="mb-6 flex flex-col items-center justify-center p-6 bg-gradient-to-br from-gray-50 to-slate-50 border border-gray-100 rounded-3xl shadow-inner text-center no-print">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Tunjukkan QR Code ini ke Penjual</p>
                    
                    {{-- QR Code Box --}}
                    <div class="bg-white p-4 rounded-2xl shadow-md border border-gray-100/60 flex items-center justify-center mb-3">
                        <div id="qrcode-container" class="w-48 h-48 flex items-center justify-center bg-gray-50 rounded-xl overflow-hidden">
                            <canvas id="qrcode-canvas" class="w-full h-full"></canvas>
                        </div>
                    </div>
                    
                    <p class="text-[10px] text-gray-400 font-bold mb-1.5 uppercase tracking-wide">Kode Unik Pengambilan</p>
                    <div class="inline-flex items-center gap-2 px-5 py-1.5 bg-white rounded-xl border border-gray-100 shadow-sm">
                        <span class="text-xl font-black text-gray-900 tracking-widest font-mono select-all uppercase">
                            {{ $order->pickup_code }}
                        </span>
                    </div>
                </div>

                {{-- Dotted Divider --}}
                <div class="border-t-2 border-dashed border-gray-100 my-6 no-print"></div>
            @endif

            {{-- Seller Info --}}
            <div class="mb-6">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Informasi Toko / Warung</h4>
                <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100/50 space-y-2 text-sm">
                    <div class="flex flex-col sm:flex-row sm:justify-between gap-1">
                        <span class="text-gray-900 font-extrabold text-base">{{ $order->seller->store_name }}</span>
                        @if(!empty($order->seller->user->phone))
                            <span class="text-gray-500 font-medium text-xs sm:text-sm">Telp: {{ $order->seller->user->phone }}</span>
                        @endif
                    </div>
                    <p class="text-gray-500 font-medium text-xs sm:text-sm leading-relaxed">{{ $order->seller->address }}</p>
                </div>
            </div>

            {{-- Dotted Divider --}}
            <div class="border-t-2 border-dashed border-gray-100 my-6"></div>

            {{-- Items Breakdown --}}
            <div class="mb-6">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Rincian Pembelian</h4>
                <div class="space-y-4">
                    @foreach($order->items as $item)
                        <div class="flex items-start justify-between gap-4 text-sm">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-black text-gray-900">{{ $item->qty }}x</span>
                                    <span class="text-gray-800 font-bold">{{ $item->product->name ?? 'Produk' }}</span>
                                    @if($item->is_surplus)
                                        <span class="px-1.5 py-0.5 bg-gradient-to-r from-red-500 to-orange-400 text-white text-[8px] font-bold rounded">SURPLUS</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-400 font-medium">Rp {{ number_format($item->price, 0, ',', '.') }} per item</p>
                            </div>
                            <span class="text-gray-900 font-black whitespace-nowrap">Rp {{ number_format($item->qty * $item->price, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Dotted Divider --}}
            <div class="border-t-2 border-dashed border-gray-100 my-6"></div>

            {{-- Total Summary --}}
            @php
                $discount = $order->discount_amount ?? 0;
                $subtotal = $order->total_amount + $discount;
            @endphp
            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 font-medium">Subtotal Pembelian</span>
                    <span class="text-gray-900 font-bold">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
                @if($discount > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 font-medium">Potongan Voucher ({{ $order->voucher_code }})</span>
                    <span class="text-green-600 font-bold">-Rp {{ number_format($discount, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 font-medium">Biaya Layanan</span>
                    <span class="text-gray-900 font-bold">Rp 0</span>
                </div>
                <div class="flex justify-between items-center border-t border-gray-100 pt-3 mt-2">
                    <span class="text-base font-extrabold text-gray-900">Total Pembayaran</span>
                    <span class="text-2xl font-black text-[#c04b36]">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- QR Code & Pickup Code Section for ready orders --}}
            @if($order->status === 'siap_diambil' && !empty($order->pickup_code))
                {{-- Dotted Divider --}}
                <div class="border-t-2 border-dashed border-gray-100 my-6"></div>

                <div class="mb-6 flex flex-col items-center justify-center p-6 bg-gradient-to-br from-gray-50 to-slate-50 border border-gray-100 rounded-3xl shadow-inner text-center">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Tunjukkan QR Code ini ke Penjual</p>
                    
                    {{-- QR Code Box --}}
                    <div class="bg-white p-4 rounded-2xl shadow-md border border-gray-100/60 flex items-center justify-center mb-3">
                        <div id="qrcode-container" class="w-48 h-48 flex items-center justify-center bg-gray-50 rounded-xl overflow-hidden">
                            <canvas id="qrcode-canvas" class="w-full h-full"></canvas>
                        </div>
                    </div>
                    
                    <p class="text-[10px] text-gray-400 font-bold mb-1.5 uppercase tracking-wide">Kode Unik Pengambilan</p>
                    <div class="inline-flex items-center gap-2 px-5 py-1.5 bg-white rounded-xl border border-gray-100 shadow-sm">
                        <span class="text-xl font-black text-gray-900 tracking-widest font-mono select-all uppercase">
                            {{ $order->pickup_code }}
                        </span>
                    </div>
                </div>
            @endif

            {{-- Cancellation Reason (if cancelled) --}}
            @if(($order->status === 'dibatalkan' || $order->status === 'hangus') && !empty($order->cancellation_reason))
                <div class="mt-8 p-4 {{ $order->status === 'hangus' ? 'bg-orange-50/50 border-orange-100' : 'bg-red-50/50 border-red-100' }} border rounded-2xl text-left">
                    <span class="text-xs font-bold {{ $order->status === 'hangus' ? 'text-orange-600' : 'text-red-600' }} uppercase tracking-wide block mb-1">
                        {{ $order->status === 'hangus' ? 'Alasan Pesanan Hangus:' : 'Alasan Pembatalan:' }}
                    </span>
                    <p class="text-sm {{ $order->status === 'hangus' ? 'text-orange-700' : 'text-red-700' }} font-semibold leading-relaxed">{{ $order->cancellation_reason }}</p>
                </div>
            @endif

            {{-- Dotted Divider --}}
            <div class="border-t-2 border-dashed border-gray-100 my-6"></div>

            {{-- Footer Note --}}
            <div class="text-center space-y-1">
                <p class="text-xs text-gray-400 font-semibold">Terima kasih telah berbelanja!</p>
                <p class="text-[10px] text-gray-400 font-medium">Dengan membeli surplus makanan, Anda telah membantu menyelamatkan bumi dari food waste.</p>
            </div>
        </div>
    </div>

    {{-- Buyer Cancellation Timer & Button --}}
    {{-- LAPIS 1 (Blade Guard): Render HANYA jika status 'menunggu_verifikasi' DAN masih dalam 15 detik --}}
    @php
        $elapsedSeconds   = now()->getTimestamp() - $order->created_at->getTimestamp();
        $remainingSeconds = max(15 - $elapsedSeconds, 0);
    @endphp

    @if(in_array($order->status, ['menunggu_verifikasi', 'diproses']) && $remainingSeconds > 0)
        <div
            x-data="{
                timeLeft:    {{ $remainingSeconds }},
                maxTime:     15,
                interval:    null,
                showCancel:  true,
                showModal:   false,
                reason:      '',
                otherReason: '',
                get pct() { return (this.timeLeft / this.maxTime) * 100; },
                init() {
                    this.startTimer();
                    this.$watch('showModal', v => {
                        if (v) this.pauseTimer();
                        else if (this.timeLeft > 0) this.startTimer();
                    });
                },
                startTimer() {
                    if (this.interval) clearInterval(this.interval);
                    this.interval = setInterval(() => {
                        if (this.timeLeft > 0) {
                            this.timeLeft--;
                        } else {
                            this.pauseTimer();
                            this.showCancel = false;
                        }
                    }, 1000);
                },
                pauseTimer() { clearInterval(this.interval); }
            }"
            x-show="showCancel || showModal"
            x-cloak
            class="mt-6 no-print"
        >
            {{-- ── Card Timer & Tombol ── --}}
            <div
                x-show="!showModal"
                class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5"
            >
                {{-- Label + angka countdown --}}
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-bold text-gray-500">Sisa Waktu Pembatalan</span>
                    <span
                        class="text-sm font-extrabold tabular-nums"
                        style="color:#c04b36"
                        x-text="timeLeft + 's'"
                    ></span>
                </div>

                {{-- ── Progress Bar ── --}}
                {{-- Outer (track): warna abu-abu Tailwind --}}
                <div class="w-full bg-gray-200 rounded-full h-2.5 mb-4 overflow-hidden">
                    {{-- Inner (fill): warna tema #c04b36 via Tailwind arbitrary, width dari Alpine template literal --}}
                    <div
                        class="bg-[#c04b36] h-2.5 rounded-full transition-all duration-1000 ease-linear"
                        :style="`width: ${(timeLeft / 15) * 100}%`"
                    ></div>
                </div>

                {{-- ── Tombol Utama ── --}}
                <button
                    id="btn-open-cancel-modal"
                    @click="showModal = true"
                    class="w-full py-3 px-4 rounded-xl font-bold text-white text-sm transition-opacity hover:opacity-90 active:opacity-75"
                    style="background:#c04b36;"
                >
                    Batalkan Pesanan
                </button>
            </div>

            {{-- ── Modal Alasan Pembatalan ── --}}
            <template x-teleport="body">
                <div
                    x-show="showModal"
                    x-cloak
                    class="fixed inset-0 z-[200] flex items-center justify-center p-4"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="cancel-modal-title"
                >
                    {{-- Backdrop --}}
                    <div
                        class="absolute inset-0 bg-black/60"
                        x-show="showModal"
                        x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-150"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        @click="showModal = false"
                    ></div>

                    {{-- Panel --}}
                    <div
                        class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-auto flex flex-col"
                        style="max-height: 90vh;"
                        x-show="showModal"
                        x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        @click.stop
                    >
                        {{-- Header --}}
                        <div class="flex items-center gap-3 px-5 pt-5 pb-4 border-b border-gray-100 shrink-0">
                            <button
                                @click="showModal = false"
                                class="w-7 h-7 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 text-xs font-bold transition-colors"
                            >✕</button>
                            <h3 id="cancel-modal-title" class="text-sm font-bold text-gray-900">Pilih Alasan Pembatalan</h3>
                        </div>

                        <form
                            action="{{ route('buyer.orders.cancel', $order->id) }}"
                            method="POST"
                            class="flex flex-col overflow-hidden"
                        >
                            @csrf
                            @method('PATCH')

                            {{-- Daftar Alasan (Dropdown) --}}
                            <div class="px-5 py-4 space-y-4 grow">
                                <div>
                                    <label for="cancellation_reason_select" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Alasan Pembatalan <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        id="cancellation_reason_select"
                                        x-model="reason"
                                        class="w-full bg-white border border-gray-200 focus:border-[#c04b36] focus:ring-1 focus:ring-[#c04b36] rounded-xl px-4 py-3 text-sm text-gray-800 font-semibold transition-all"
                                    >
                                        <option value="" disabled selected>Pilih Alasan Pembatalan</option>
                                        @php
                                            $reasons = [
                                                'Pembayaran gagal',
                                                'Posisi Driver terlalu jauh',
                                                'Saya berubah pikiran',
                                                'Lupa pakai Voucher',
                                                'Kesalahan alamat atau nomor telepon',
                                                'Driver ingin membatalkan',
                                                'Driver tidak memiliki uang yang cukup',
                                                'Driver tidak bisa dihubungi',
                                                'Lainnya',
                                            ];
                                        @endphp
                                        @foreach($reasons as $r)
                                            <option value="{{ $r }}">{{ $r }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Textarea Lainnya --}}
                                <div x-show="reason === 'Lainnya'" x-cloak class="pt-1">
                                    <textarea
                                        x-model="otherReason"
                                        name="cancellation_reason_other"
                                        rows="3"
                                        class="w-full border border-gray-200 focus:border-[#c04b36] focus:ring-1 focus:ring-[#c04b36] rounded-xl px-4 py-3 text-sm text-gray-800 placeholder-gray-400 font-medium transition-all resize-none"
                                        placeholder="Tulis alasan lainnya..."
                                    ></textarea>
                                </div>
                            </div>

                            {{-- Hidden field --}}
                            <input
                                type="hidden"
                                name="cancellation_reason"
                                :value="reason === 'Lainnya' ? otherReason : reason"
                            >

                            {{-- ── Tombol Submit (always visible, tidak terpotong) ── --}}
                            <div class="px-5 pb-5 pt-3 border-t border-gray-100 shrink-0">
                                <button
                                    id="btn-submit-cancel"
                                    type="submit"
                                    :disabled="!reason || (reason === 'Lainnya' && !otherReason)"
                                    class="w-full py-3 px-4 rounded-xl font-bold text-white text-sm transition-opacity hover:opacity-90 disabled:opacity-40 disabled:cursor-not-allowed"
                                    style="background:#c04b36;"
                                >
                                    Batalkan Pesanan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </template>
        </div>
    @endif

    {{-- Bottom Actions (no-print) --}}
    <div class="flex flex-col sm:flex-row gap-3 mt-8 no-print justify-center">
        @if($order->status === 'selesai')
            <a href="{{ route('buyer.store.show', $order->seller_id) }}" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-gray-900 text-white font-bold text-sm rounded-2xl shadow-lg hover:shadow-xl hover:bg-gray-800 transition-all duration-300">
                Beli Lagi di Toko Ini
            </a>
            
            <a href="{{ route('buyer.complaint.create', ['seller' => $order->seller_id]) }}" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-white border-2 border-red-200 text-red-600 font-bold text-sm rounded-2xl shadow-sm hover:bg-red-50/50 transition-all duration-300">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Ajukan Komplain
            </a>
        @elseif($order->status === 'dibatalkan' || $order->status === 'hangus')
            <a href="{{ route('buyer.store.show', $order->seller_id) }}" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-gray-900 text-white font-bold text-sm rounded-2xl shadow-lg hover:shadow-xl hover:bg-gray-800 transition-all duration-300">
                Kunjungi Toko Kembali
            </a>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if($order->status === 'siap_diambil' && !empty($order->pickup_code))
            if (document.getElementById('qrcode-canvas')) {
                var qr = new QRious({
                    element: document.getElementById('qrcode-canvas'),
                    value: '{{ $order->pickup_code }}',
                    size: 200,
                    background: '#ffffff',
                    foreground: '#0f172a',
                    level: 'H'
                });
            }
        @endif
    });
</script>
@endpush
