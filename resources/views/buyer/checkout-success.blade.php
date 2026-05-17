@extends('layouts.buyer')

@section('title', 'Pesanan Berhasil')

@section('content')
<div class="max-w-2xl mx-auto text-center py-8">
    {{-- Success Icon --}}
    <div class="w-24 h-24 rounded-full mx-auto mb-6 flex items-center justify-center {{ $order->status === 'diproses' ? 'bg-green-50' : 'bg-orange-50' }}">
        @if($order->status === 'diproses')
            <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        @else
            <svg class="w-12 h-12 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        @endif
    </div>

    {{-- Title --}}
    @if($order->status === 'diproses')
        <h1 class="text-3xl font-black text-gray-900 tracking-tight mb-2">Pesanan Berhasil!</h1>
        <p class="text-gray-500 font-medium mb-8">Pesanan Anda sedang diproses oleh toko.</p>
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
                @else
                    <span class="inline-flex px-3 py-1 rounded-lg bg-orange-100 text-orange-700 text-xs font-bold uppercase tracking-wider" id="order-status">Menunggu Verifikasi</span>
                @endif
            </div>
        </div>

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

    {{-- Actions --}}
    <div class="flex flex-col gap-3">
        <a href="{{ route('buyer.menu') }}" class="inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-[#c04b36] text-white font-bold text-sm rounded-2xl shadow-lg hover:shadow-xl hover:bg-[#a33d2b] transition-all duration-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Cari Makanan Lainnya
        </a>
    </div>
</div>
@endsection
