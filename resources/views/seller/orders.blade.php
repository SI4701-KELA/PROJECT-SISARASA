@extends('layouts.seller')

@section('title', 'Daftar Pesanan')

@section('content')
<div class="max-w-6xl" x-data="{ showProofModal: false, proofImage: '', showRejectModal: false, rejectOrderId: null }">
    {{-- Page Header --}}
    <div class="flex items-center gap-4 mb-6">
        <div class="w-10 h-10 rounded-2xl bg-red-50 flex items-center justify-center text-terracotta shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Daftar Pesanan</h1>
            <p class="text-xs text-gray-500 font-medium mt-0.5">Kelola pesanan masuk ke toko {{ $seller->store_name }}.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl shadow-sm font-medium text-sm flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl shadow-sm font-medium text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Tabs --}}
    <div class="flex gap-2 mb-6 overflow-x-auto pb-2" id="order-tabs">
        <a href="{{ route('seller.orders', ['tab' => 'baru']) }}"
           class="flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold transition-all whitespace-nowrap {{ $tab === 'baru' ? 'bg-[#c04b36] text-white shadow-sm' : 'bg-white border border-gray-100 text-gray-500 hover:border-terracotta hover:text-terracotta' }}">
            Pesanan Baru
            @if($countBaru > 0)
                <span class="bg-white/20 text-white text-[10px] font-black w-5 h-5 flex items-center justify-center rounded-full {{ $tab === 'baru' ? '' : 'bg-red-500 !text-white' }}">{{ $countBaru }}</span>
            @endif
        </a>
        <a href="{{ route('seller.orders', ['tab' => 'diproses']) }}"
           class="flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold transition-all whitespace-nowrap {{ $tab === 'diproses' ? 'bg-[#c04b36] text-white shadow-sm' : 'bg-white border border-gray-100 text-gray-500 hover:border-terracotta hover:text-terracotta' }}">
            Diproses
            @if($countDiproses > 0)
                <span class="text-[10px] font-black w-5 h-5 flex items-center justify-center rounded-full {{ $tab === 'diproses' ? 'bg-white/20 text-white' : 'bg-blue-100 text-blue-600' }}">{{ $countDiproses }}</span>
            @endif
        </a>
        <a href="{{ route('seller.orders', ['tab' => 'siap']) }}"
           class="flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold transition-all whitespace-nowrap {{ $tab === 'siap' ? 'bg-[#c04b36] text-white shadow-sm' : 'bg-white border border-gray-100 text-gray-500 hover:border-terracotta hover:text-terracotta' }}">
            Siap Diambil
            @if($countSiap > 0)
                <span class="text-[10px] font-black w-5 h-5 flex items-center justify-center rounded-full {{ $tab === 'siap' ? 'bg-white/20 text-white' : 'bg-green-100 text-green-600' }}">{{ $countSiap }}</span>
            @endif
        </a>
        <a href="{{ route('seller.orders', ['tab' => 'selesai']) }}"
           class="px-5 py-2.5 rounded-full text-sm font-bold transition-all whitespace-nowrap {{ $tab === 'selesai' ? 'bg-[#c04b36] text-white shadow-sm' : 'bg-white border border-gray-100 text-gray-500 hover:border-terracotta hover:text-terracotta' }}">
            Riwayat
        </a>
    </div>

    {{-- Order List --}}
    @if($orders->isEmpty())
        <div class="text-center py-16 bg-white rounded-[24px] border border-gray-100 shadow-sm">
            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <h3 class="text-base font-bold text-gray-900 mb-1">Tidak ada pesanan</h3>
            <p class="text-sm text-gray-500 font-medium">Belum ada pesanan di tab ini.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($orders as $order)
            <div class="bg-white rounded-[20px] border border-gray-100 shadow-sm overflow-hidden order-card" id="order-{{ $order->id }}">
                {{-- Order Header --}}
                <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <span class="text-sm font-black text-gray-900">#{{ $order->id }}</span>
                        <span class="text-xs text-gray-400 font-medium">{{ $order->created_at->format('d M Y, H:i') }}</span>
                        <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-lg
                            {{ $order->payment_method === 'qris' ? 'bg-blue-50 text-blue-600' : 'bg-green-50 text-green-600' }}">
                            {{ strtoupper($order->payment_method) }}
                        </span>
                    </div>
                    <div class="flex items-center gap-3">
                        @php
                            $statusColors = [
                                'menunggu_verifikasi' => 'bg-orange-100 text-orange-700',
                                'diproses' => 'bg-blue-100 text-blue-700',
                                'siap_diambil' => 'bg-green-100 text-green-700',
                                'selesai' => 'bg-gray-100 text-gray-600',
                                'dibatalkan' => 'bg-red-100 text-red-700',
                            ];
                            $statusLabels = [
                                'menunggu_verifikasi' => 'Menunggu Verifikasi',
                                'diproses' => 'Diproses',
                                'siap_diambil' => 'Siap Diambil',
                                'selesai' => 'Selesai',
                                'dibatalkan' => 'Dibatalkan',
                            ];
                        @endphp
                        <span class="text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-lg {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-600' }} order-status-badge">
                            {{ $statusLabels[$order->status] ?? $order->status }}
                        </span>
                    </div>
                </div>

                {{-- Order Body --}}
                <div class="px-6 py-4">
                    <div class="flex items-start justify-between gap-6">
                        {{-- Buyer Info --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Pembeli</p>
                            <p class="text-sm font-bold text-gray-900">{{ $order->buyer->name ?? '-' }}</p>
                            <p class="text-xs text-gray-400 font-medium">{{ $order->buyer->phone ?? '-' }}</p>
                        </div>

                        {{-- Items --}}
                        <div class="flex-1">
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Item Pesanan</p>
                            @foreach($order->items as $item)
                                <div class="flex items-center justify-between text-sm py-0.5">
                                    <span class="text-gray-700 font-medium">
                                        {{ $item->product->name ?? 'Produk' }}
                                        @if($item->is_surplus)
                                            <span class="text-[8px] px-1 py-0.5 bg-red-100 text-red-600 font-bold rounded ml-1">SURPLUS</span>
                                        @endif
                                    </span>
                                    <span class="text-gray-500 font-medium">{{ $item->qty }} × Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>

                        {{-- Total --}}
                        <div class="text-right shrink-0">
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Total</p>
                            <p class="text-lg font-black text-gray-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    {{-- Cancellation Reason (if applicable) --}}
                    @if($order->status === 'dibatalkan' && $order->cancellation_reason)
                        <div class="mt-4 p-3 bg-red-50 border border-red-100 rounded-xl">
                            <p class="text-[10px] font-bold text-red-400 uppercase tracking-widest mb-1">Alasan Pembatalan</p>
                            <p class="text-sm text-red-700 font-medium">{{ $order->cancellation_reason }}</p>
                        </div>
                    @endif
                </div>

                {{-- Action Buttons --}}
                <div class="px-6 py-3 bg-gray-50/50 border-t border-gray-50 flex items-center justify-end gap-3">
                    {{-- Lihat Bukti Transfer (for QRIS orders) --}}
                    @if($order->payment_method === 'qris' && $order->payment_proof)
                        <button type="button"
                                class="px-4 py-2 text-xs font-bold text-blue-600 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors btn-lihat-bukti"
                                @click="showProofModal = true; proofImage = '{{ Storage::url($order->payment_proof) }}'">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Lihat Bukti Transfer
                        </button>
                    @endif

                    {{-- Skenario A: Terima Pembayaran (menunggu_verifikasi → diproses) --}}
                    @if($order->status === 'menunggu_verifikasi')
                        <form action="{{ route('seller.orders.accept', $order->id) }}" method="POST" class="inline">
                            @csrf @method('PATCH')
                            <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-green-500 rounded-xl hover:bg-green-600 transition-colors btn-terima">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                Terima Pembayaran
                            </button>
                        </form>

                        {{-- Skenario B: Tolak Pembayaran (menunggu_verifikasi → dibatalkan) --}}
                        <button type="button"
                                class="px-4 py-2 text-xs font-bold text-red-600 bg-red-50 rounded-xl hover:bg-red-100 transition-colors btn-tolak"
                                @click="showRejectModal = true; rejectOrderId = {{ $order->id }}">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            Tolak Pembayaran
                        </button>
                    @endif

                    {{-- Skenario C: Makanan Siap (diproses → siap_diambil) --}}
                    @if($order->status === 'diproses')
                        <form action="{{ route('seller.orders.ready', $order->id) }}" method="POST" class="inline">
                            @csrf @method('PATCH')
                            <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-[#c04b36] rounded-xl hover:bg-[#a33d2b] transition-colors btn-siap">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                Makanan Siap
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    @endif

    {{-- Modal: Lihat Bukti Transfer --}}
    <div x-show="showProofModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="showProofModal = false" style="display: none;" id="proof-modal">
        <div class="bg-white rounded-[24px] shadow-2xl max-w-lg w-full p-6" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">Bukti Transfer</h3>
                <button @click="showProofModal = false" class="w-8 h-8 rounded-xl bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="bg-gray-50 rounded-2xl p-2 flex items-center justify-center">
                <img :src="proofImage" alt="Bukti Transfer" class="max-w-full max-h-[60vh] rounded-xl object-contain" id="proof-image">
            </div>
        </div>
    </div>

    {{-- Modal: Tolak Pembayaran --}}
    <div x-show="showRejectModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="showRejectModal = false" style="display: none;" id="reject-modal">
        <div class="bg-white rounded-[24px] shadow-2xl max-w-md w-full p-6" @click.stop>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.27 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Tolak Pembayaran</h3>
                    <p class="text-xs text-gray-500 font-medium">Pesanan akan dibatalkan secara permanen.</p>
                </div>
            </div>

            <form :action="'/seller/orders/' + rejectOrderId + '/reject'" method="POST" id="reject-form">
                @csrf @method('PATCH')
                <div class="mb-4">
                    <label for="cancellation_reason" class="block text-sm font-bold text-gray-700 mb-2">Alasan Penolakan <span class="text-red-500">*</span></label>
                    <textarea name="cancellation_reason" id="cancellation_reason" rows="3" required minlength="5"
                              placeholder="Contoh: Nominal transfer kurang 5 ribu"
                              class="w-full bg-white border border-gray-200 focus:border-[#c04b36] focus:ring-1 focus:ring-[#c04b36] rounded-xl px-4 py-3 text-sm text-gray-800 placeholder-gray-400 font-medium transition-all resize-none"></textarea>
                </div>
                <div class="flex gap-3 justify-end">
                    <button type="button" @click="showRejectModal = false" class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-red-500 rounded-xl hover:bg-red-600 transition-colors">
                        Tolak & Batalkan Pesanan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
