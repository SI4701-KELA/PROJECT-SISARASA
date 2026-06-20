@extends('layouts.buyer')

@section('title', 'Pesanan Berhasil')

@section('content')
<div class="max-w-2xl mx-auto text-center py-8">
    {{-- Success Icon --}}
    <div class="w-24 h-24 rounded-full mx-auto mb-6 flex items-center justify-center {{ in_array($order->status, ['diproses', 'siap_diambil']) ? 'bg-green-50' : ($order->status === 'dibatalkan' ? 'bg-red-50' : 'bg-orange-50') }}">
        @if(in_array($order->status, ['diproses', 'siap_diambil']))
            <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        @elseif($order->status === 'dibatalkan')
            <svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        @else
            <svg class="w-12 h-12 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        @endif
    </div>

    {{-- Title --}}
    @if($order->status === 'diproses')
        <h1 class="text-3xl font-black text-gray-900 tracking-tight mb-2">Pesanan Diproses!</h1>
        <p class="text-gray-500 font-medium mb-8">Pesanan Anda sedang diproses oleh toko.</p>
    @elseif($order->status === 'siap_diambil')
        <h1 class="text-3xl font-black text-gray-900 tracking-tight mb-2">Pesanan Siap Diambil!</h1>
        <p class="text-gray-500 font-medium mb-8">Silakan ambil pesanan Anda di toko.</p>
    @elseif($order->status === 'dibatalkan')
        <h1 class="text-3xl font-black text-gray-900 tracking-tight mb-2">Pesanan Dibatalkan</h1>
        <p class="text-gray-500 font-medium mb-8">Pesanan ini telah dibatalkan.</p>
    @else
        <h1 class="text-3xl font-black text-gray-900 tracking-tight mb-2">Menunggu Verifikasi</h1>
        <p class="text-gray-500 font-medium mb-8">Bukti pembayaran Anda sedang diverifikasi oleh toko.</p>
    @endif

    {{-- Order Card --}}
    <div class="bg-white rounded-[24px] border border-gray-100 shadow-sm p-8 text-left mb-6">
        <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100">
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Order ID</p>
                <p class="text-xl font-black text-gray-900" id="order-id">#{{ $order->id }}</p>
            </div>
            <div class="text-right">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Status</p>
                @if($order->status === 'diproses')
                    <span class="inline-flex px-3 py-1 rounded-lg bg-green-100 text-green-700 text-xs font-bold uppercase tracking-wider" id="order-status">Diproses</span>
                @elseif($order->status === 'siap_diambil')
                    <span class="inline-flex px-3 py-1 rounded-lg bg-blue-100 text-blue-700 text-xs font-bold uppercase tracking-wider" id="order-status">Siap Diambil</span>
                @elseif($order->status === 'dibatalkan')
                    <span class="inline-flex px-3 py-1 rounded-lg bg-red-100 text-red-700 text-xs font-bold uppercase tracking-wider" id="order-status">Dibatalkan</span>
                @else
                    <span class="inline-flex px-3 py-1 rounded-lg bg-orange-100 text-orange-700 text-xs font-bold uppercase tracking-wider" id="order-status">Menunggu Verifikasi</span>
                @endif
            </div>
        </div>

        @if($order->status === 'diproses')
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl shadow-sm text-sm">
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
            }">
                <div x-show="isExpired" style="display: none" class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl shadow-sm text-sm font-bold">
                    Batas waktu pengambilan telah terlewat.
                </div>
                <div x-show="!isExpired" style="display: none" class="mb-6 bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-xl shadow-sm text-sm">
                    Harap ambil pesanan Anda dalam waktu: <span class="font-bold text-red-600 font-mono text-base ml-1" x-text="formatTime()"></span>
                    <br><span class="text-xs text-gray-600">(Batas Maksimal: {{ $deadlineTime }} WIB)</span>
                </div>
            </div>
        @endif

        @if($order->status === 'siap_diambil' && $order->pickup_code)
            {{-- QR Code & Pickup Code Section --}}
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

        {{-- Order Details --}}
        <div class="space-y-2 mb-6">
            <div class="flex justify-between text-sm">
                <span class="text-gray-500 font-medium">Toko</span>
                <span class="text-gray-900 font-bold">{{ $order->seller->store_name ?? '-' }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500 font-medium">Metode Pembayaran</span>
                <span class="text-gray-900 font-bold uppercase" id="payment-method-display">{{ $order->payment_method }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500 font-medium">Tanggal Pesanan</span>
                <span class="text-gray-900 font-bold">{{ $order->created_at->format('d M Y, H:i') }}</span>
            </div>
        </div>

        {{-- Items --}}
        <div class="border-t border-gray-100 pt-4 space-y-3">
            @foreach($order->items as $item)
            <div class="flex items-center justify-between py-1">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-700 font-medium">{{ $item->product->name ?? 'Produk' }}</span>
                    @if($item->is_surplus)
                        <span class="px-1.5 py-0.5 bg-gradient-to-r from-red-500 to-orange-400 text-white text-[8px] font-bold rounded">SURPLUS</span>
                    @endif
                </div>
                <span class="text-sm text-gray-900 font-bold">{{ $item->qty }} × Rp {{ number_format($item->price, 0, ',', '.') }}</span>
            </div>
            @endforeach
        </div>

        {{-- Total --}}
        <div class="border-t border-gray-100 mt-4 pt-4 flex items-center justify-between">
            <p class="text-sm font-bold text-gray-500">Total Tagihan</p>
            <p class="text-2xl font-black text-gray-900" id="order-total">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Buyer Cancellation Timer & Button --}}
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
            class="mt-6 mb-6 no-print"
        >
            {{-- ── Card Timer & Tombol ── --}}
            <div
                x-show="!showModal"
                class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 text-left"
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
                <div class="w-full bg-gray-200 rounded-full h-2.5 mb-4 overflow-hidden">
                    <div
                        class="bg-[#c04b36] h-2.5 rounded-full transition-all duration-1000 ease-linear"
                        :style="`width: ${(timeLeft / 15) * 100}%`"
                    ></div>
                </div>

                {{-- ── Tombol Utama ── --}}
                <button
                    @click="showModal = true"
                    type="button"
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
                        class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-auto flex flex-col text-left"
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
                                type="button"
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

                            {{-- Tombol Submit --}}
                            <div class="px-5 pb-5 pt-3 border-t border-gray-100 shrink-0">
                                <button
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

    {{-- Actions --}}
    <div class="flex flex-col gap-3">
        <a href="{{ route('buyer.menu') }}" class="inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-[#c04b36] text-white font-bold text-sm rounded-2xl shadow-lg hover:shadow-xl hover:bg-[#a33d2b] transition-all duration-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Cari Makanan Lainnya
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if($order->status === 'siap_diambil' && !empty($order->pickup_code))
            var qr = new QRious({
                element: document.getElementById('qrcode-canvas'),
                value: '{{ $order->pickup_code }}',
                size: 200,
                background: '#ffffff',
                foreground: '#0f172a',
                level: 'H'
            });
        @endif

        @if(in_array($order->status, ['menunggu_verifikasi', 'diproses', 'siap_diambil']))
            // Auto refresh to check status updates every 30 seconds
            setTimeout(function() {
                window.location.reload();
            }, 30000);
        @endif
    });
</script>
@endpush
