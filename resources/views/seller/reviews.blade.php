@extends('layouts.seller')

@section('title', 'Ulasan Pelanggan')

@section('content')
<div class="max-w-5xl mx-auto">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-black text-gray-900 tracking-tight">Ulasan Pelanggan</h1>
        <p class="text-gray-500 font-medium mt-1">Dengarkan umpan balik dari pembeli untuk terus meningkatkan kualitas produk dan pelayanan tokomu.</p>
    </div>

    @if($totalReviews === 0)
        {{-- Empty State --}}
        <div class="text-center py-20 bg-white rounded-3xl border border-gray-100/80 shadow-sm max-w-2xl mx-auto px-6">
            <div class="w-32 h-32 mx-auto bg-red-50/50 rounded-full flex items-center justify-center mb-8 relative">
                <div class="absolute inset-0 bg-red-50 rounded-full animate-ping opacity-25"></div>
                <svg class="w-16 h-16 text-[#c04b36] relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
            </div>
            <h3 class="text-2xl font-black text-gray-900 mb-3">Belum Ada Ulasan</h3>
            <p class="text-gray-500 font-medium mb-4 max-w-md mx-auto">Toko Anda belum menerima ulasan dari pembeli. Terus sajikan surplus food terbaik dan ingatkan pelanggan untuk memberikan ulasan!</p>
        </div>
    @else
        {{-- Statistics Section --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            {{-- Big Score Card --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 lg:p-8 flex flex-col justify-center items-center text-center">
                <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Rata-Rata Rating</p>
                <div class="flex items-baseline gap-1">
                    <span class="text-6xl font-black text-gray-900 leading-none">{{ number_format($averageRating, 1) }}</span>
                    <span class="text-xl font-bold text-gray-400">/ 5.0</span>
                </div>
                
                {{-- Stars --}}
                <div class="flex items-center gap-1 mt-4">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="w-6 h-6 {{ $i <= round($averageRating) ? 'text-amber-400 fill-current' : 'text-gray-200' }}" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endfor
                </div>
                
                <p class="text-xs font-bold text-gray-400 mt-4 uppercase tracking-wider">Berdasarkan {{ $totalReviews }} Ulasan Pelanggan</p>
            </div>

            {{-- Star Breakdown Card --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 lg:p-8 md:col-span-2 flex flex-col justify-between">
                <h3 class="text-base font-black text-gray-900 mb-4 uppercase tracking-wider">Distribusi Ulasan</h3>
                
                <div class="space-y-3">
                    @foreach([5, 4, 3, 2, 1] as $star)
                        @php
                            $count = $starDistribution[$star];
                            $percentage = $starPercentages[$star];
                        @endphp
                        <div class="flex items-center gap-4">
                            <span class="text-xs font-bold text-gray-500 w-3 flex items-center justify-end">{{ $star }}</span>
                            <svg class="w-3.5 h-3.5 text-amber-400 fill-current shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            
                            {{-- Progress Bar --}}
                            <div class="flex-1 bg-gray-100 h-2.5 rounded-full overflow-hidden">
                                <div class="bg-gradient-to-r from-amber-400 to-orange-400 h-2.5 rounded-full transition-all duration-500"
                                     style="width: {{ $percentage }}%"></div>
                            </div>
                            
                            {{-- Counts / Percentage --}}
                            <span class="text-xs font-bold text-gray-400 w-12 text-left">{{ $percentage }}% <span class="font-medium">({{ $count }})</span></span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Reviews List Feed --}}
        <div class="space-y-6">
            <h3 class="text-xl font-black text-gray-900 tracking-tight">Semua Ulasan</h3>
            
            <div class="grid grid-cols-1 gap-4">
                @foreach($reviews as $review)
                    <div class="bg-white rounded-3xl border border-gray-100/80 shadow-sm p-6 lg:p-8 hover:shadow-md transition-all duration-300">
                        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                            
                            {{-- Left side: User & Comment --}}
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    {{-- User Avatar --}}
                                    <div class="w-10 h-10 rounded-full bg-[#c04b36]/10 flex items-center justify-center text-[#c04b36] font-black text-sm">
                                        {{ strtoupper(substr($review->buyer->name ?? 'P', 0, 1)) }}
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-black text-gray-900">{{ $review->buyer->name ?? 'Pembeli' }}</h4>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">{{ $review->created_at->diffForHumans() }} &bull; {{ $review->created_at->format('d M Y, H:i') }}</p>
                                    </div>
                                </div>

                                {{-- Rating --}}
                                <div class="flex items-center gap-0.5 mb-4">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-4.5 h-4.5 {{ $i <= $review->rating ? 'text-amber-400 fill-current' : 'text-gray-200' }}" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>

                                {{-- Comment Text --}}
                                @if($review->comment)
                                    <p class="text-sm text-gray-700 font-medium leading-relaxed bg-gray-50 border border-gray-100/50 rounded-2xl p-4 italic">
                                        "{{ $review->comment }}"
                                    </p>
                                @else
                                    <p class="text-xs text-gray-400 italic">Pembeli tidak meninggalkan ulasan tertulis (hanya memberikan rating bintang).</p>
                                @endif
                            </div>

                            {{-- Right side: Order info --}}
                            <div class="md:w-60 border-t md:border-t-0 md:border-l border-gray-100 pt-4 md:pt-0 md:pl-6 shrink-0 flex flex-col justify-between self-stretch">
                                <div>
                                    <div class="flex items-center gap-1.5 mb-2">
                                        <span class="px-2 py-0.5 bg-gray-100 text-gray-500 text-[10px] font-bold rounded uppercase tracking-wider">
                                            #{{ $review->order_id }}
                                        </span>
                                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Detail Pembelian</span>
                                    </div>
                                    
                                    {{-- Order Items --}}
                                    <div class="space-y-1">
                                        @foreach($review->order->items as $item)
                                            <div class="flex justify-between items-center text-xs">
                                                <span class="text-gray-500 font-medium truncate max-w-[140px]">{{ $item->product->name ?? 'Produk' }}</span>
                                                <span class="font-bold text-gray-700 whitespace-nowrap">{{ $item->qty }}x</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="mt-4 border-t border-gray-50 pt-2 flex justify-between items-center text-xs">
                                    <span class="text-gray-400 font-bold uppercase tracking-wider text-[9px]">Total Belanja</span>
                                    <span class="font-black text-gray-900">Rp {{ number_format($review->order->total_amount, 0, ',', '.') }}</span>
                                </div>
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
