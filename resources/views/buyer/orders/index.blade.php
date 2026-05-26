@extends('layouts.buyer')

@section('title', 'Riwayat Pesanan')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Riwayat Pesanan</h1>
            <p class="text-gray-500 font-medium mt-2">Pantau pesanan Anda dari toko favorit</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex space-x-2 mb-8 overflow-x-auto pb-2 scrollbar-hide">
        <a href="{{ route('buyer.orders.index', ['tab' => 'semua']) }}" 
           class="px-5 py-2.5 rounded-full text-sm font-bold transition-all whitespace-nowrap {{ $tab === 'semua' ? 'bg-gray-900 text-white shadow-md' : 'bg-white text-gray-500 border border-gray-200 hover:bg-gray-50' }}">
            Semua ({{ $countSemua }})
        </a>
        <a href="{{ route('buyer.orders.index', ['tab' => 'baru']) }}" 
           class="px-5 py-2.5 rounded-full text-sm font-bold transition-all whitespace-nowrap {{ $tab === 'baru' ? 'bg-orange-500 text-white shadow-md' : 'bg-white text-gray-500 border border-gray-200 hover:bg-gray-50' }}">
            Menunggu Verifikasi ({{ $countBaru }})
        </a>
        <a href="{{ route('buyer.orders.index', ['tab' => 'diproses']) }}" 
           class="px-5 py-2.5 rounded-full text-sm font-bold transition-all whitespace-nowrap {{ $tab === 'diproses' ? 'bg-blue-500 text-white shadow-md' : 'bg-white text-gray-500 border border-gray-200 hover:bg-gray-50' }}">
            Diproses ({{ $countDiproses }})
        </a>
        <a href="{{ route('buyer.orders.index', ['tab' => 'siap']) }}" 
           class="px-5 py-2.5 rounded-full text-sm font-bold transition-all whitespace-nowrap {{ $tab === 'siap' ? 'bg-green-500 text-white shadow-md' : 'bg-white text-gray-500 border border-gray-200 hover:bg-gray-50' }}">
            Siap Diambil ({{ $countSiap }})
        </a>
        <a href="{{ route('buyer.orders.index', ['tab' => 'selesai']) }}" 
           class="px-5 py-2.5 rounded-full text-sm font-bold transition-all whitespace-nowrap {{ $tab === 'selesai' ? 'bg-gray-900 text-white shadow-md' : 'bg-white text-gray-500 border border-gray-200 hover:bg-gray-50' }}">
            Selesai/Dibatalkan ({{ $countSelesai }})
        </a>
    </div>

    {{-- Order List --}}
    @if($orders->count() > 0)
        <div class="space-y-6">
            @foreach($orders as $order)
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 lg:p-8">
                    <div class="flex flex-col lg:flex-row gap-6">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded-lg uppercase tracking-wider">
                                    #{{ $order->id }}
                                </span>
                                <span class="text-sm font-medium text-gray-500">{{ $order->created_at->format('d M Y, H:i') }}</span>
                                
                                @if($order->status === 'menunggu_verifikasi')
                                    <span class="ml-auto px-3 py-1 bg-orange-100 text-orange-700 text-xs font-bold rounded-lg uppercase">Menunggu</span>
                                @elseif($order->status === 'diproses')
                                    <span class="ml-auto px-3 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-lg uppercase">Diproses</span>
                                @elseif($order->status === 'siap_diambil')
                                    <span class="ml-auto px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-lg uppercase">Siap Diambil</span>
                                @elseif($order->status === 'selesai')
                                    <span class="ml-auto px-3 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded-lg uppercase">Selesai</span>
                                @elseif($order->status === 'dibatalkan')
                                    <span class="ml-auto px-3 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-lg uppercase">Dibatalkan</span>
                                @endif
                            </div>

                            <div class="mb-6">
                                <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $order->seller->store_name }}</h3>
                                <p class="text-sm text-gray-500">{{ count($order->items) }} Item Pesanan</p>
                            </div>

                            <div class="space-y-3">
                                @foreach($order->items as $item)
                                    <div class="flex justify-between items-center text-sm">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-gray-700">{{ $item->qty }}x</span>
                                            <span class="text-gray-600">{{ $item->product->name ?? 'Produk' }}</span>
                                            @if($item->is_surplus)
                                                <span class="px-1.5 py-0.5 bg-gradient-to-r from-red-500 to-orange-400 text-white text-[8px] font-bold rounded">SURPLUS</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="lg:w-64 flex flex-col justify-between border-t lg:border-t-0 lg:border-l border-gray-100 pt-6 lg:pt-0 lg:pl-6">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total Belanja</p>
                                <p class="text-xl font-black text-gray-900 mb-4">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                            </div>
                            
                            <a href="{{ route('buyer.checkout.success', $order->id) }}" 
                               class="w-full inline-flex items-center justify-center px-4 py-3 bg-white border-2 border-gray-900 text-gray-900 hover:bg-gray-900 hover:text-white font-bold rounded-xl transition-colors text-sm">
                                Lacak Pesanan
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-20 bg-white rounded-3xl border border-gray-100">
            <div class="w-24 h-24 mx-auto bg-gray-50 rounded-full flex items-center justify-center mb-6">
                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Belum ada pesanan</h3>
            <p class="text-gray-500 mb-8">Anda belum memiliki pesanan dalam kategori ini.</p>
            <a href="{{ route('buyer.menu') }}" class="inline-flex px-6 py-3 bg-[#c04b36] hover:bg-[#a33d2b] text-white font-bold rounded-xl transition-colors">
                Mulai Belanja
            </a>
        </div>
    @endif
</div>
@endsection
