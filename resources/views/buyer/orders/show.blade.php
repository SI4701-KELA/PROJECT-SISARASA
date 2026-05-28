@extends('layouts.buyer')

@section('title', 'Detail Invoice #' . $order->id)

@push('styles')
<style>
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
                        @else
                            <span class="px-2.5 py-0.5 bg-gray-50 text-gray-600 text-xs font-bold rounded border border-gray-200 uppercase tracking-wide">{{ $order->status }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Dotted Divider --}}
            <div class="border-t-2 border-dashed border-gray-100 my-6"></div>

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
            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 font-medium">Subtotal Pembelian</span>
                    <span class="text-gray-900 font-bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 font-medium">Biaya Layanan</span>
                    <span class="text-gray-900 font-bold">Rp 0</span>
                </div>
                <div class="flex justify-between items-center border-t border-gray-100 pt-3 mt-2">
                    <span class="text-base font-extrabold text-gray-900">Total Pembayaran</span>
                    <span class="text-2xl font-black text-[#c04b36]">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Cancellation Reason (if cancelled) --}}
            @if($order->status === 'dibatalkan' && !empty($order->cancellation_reason))
                <div class="mt-8 p-4 bg-red-50/50 border border-red-100 rounded-2xl text-left">
                    <span class="text-xs font-bold text-red-600 uppercase tracking-wide block mb-1">Alasan Pembatalan:</span>
                    <p class="text-sm text-red-700 font-semibold leading-relaxed">{{ $order->cancellation_reason }}</p>
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
        @elseif($order->status === 'dibatalkan')
            <a href="{{ route('buyer.store.show', $order->seller_id) }}" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-gray-900 text-white font-bold text-sm rounded-2xl shadow-lg hover:shadow-xl hover:bg-gray-800 transition-all duration-300">
                Kunjungi Toko Kembali
            </a>
        @endif
    </div>
</div>
@endsection
