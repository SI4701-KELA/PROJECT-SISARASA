@extends('layouts.buyer')

@section('title', 'Riwayat Pesanan')

@section('content')
<div class="max-w-5xl mx-auto" x-data="reviewModalState()">
    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="mb-6 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-medium flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm font-medium flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            {{ session('error') }}
        </div>
    @endif
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Riwayat Pesanan</h1>
            <p class="text-gray-500 font-medium mt-2">Pantau pesanan aktif dan rekam jejak transaksi Anda</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex space-x-2 mb-8 overflow-x-auto pb-2 scrollbar-hide">
        <a href="{{ route('buyer.orders.index', ['tab' => 'riwayat']) }}" 
           class="px-6 py-3 rounded-full text-sm font-bold transition-all whitespace-nowrap {{ $tab === 'riwayat' ? 'bg-[#c04b36] text-white shadow-md shadow-red-100' : 'bg-white text-gray-500 border border-gray-100 hover:bg-gray-50' }}">
            Riwayat Transaksi ({{ $countRiwayat }})
        </a>
        <a href="{{ route('buyer.orders.index', ['tab' => 'aktif']) }}" 
           class="px-6 py-3 rounded-full text-sm font-bold transition-all whitespace-nowrap {{ $tab === 'aktif' ? 'bg-[#c04b36] text-white shadow-md shadow-red-100' : 'bg-white text-gray-500 border border-gray-100 hover:bg-gray-50' }}">
            Pesanan Aktif ({{ $countAktif }})
        </a>
    </div>

    {{-- Order List --}}
    @if($orders->count() > 0)
        <div class="space-y-6">
            @foreach($orders as $order)
                @if($tab === 'riwayat')
                    {{-- Safe Card wrapper using div to prevent nested interaction issues --}}
                    <div class="block bg-white rounded-3xl border border-gray-100/80 shadow-sm p-6 lg:p-8 hover:shadow-md hover:border-red-50 transition-all duration-300 group">
                        <div class="flex flex-col lg:flex-row gap-6">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-4">
                                    <span class="px-3 py-1 bg-gray-50 text-gray-500 text-xs font-bold rounded-lg border border-gray-100 uppercase tracking-wider">
                                        #{{ $order->id }}
                                    </span>
                                    <span class="text-sm font-medium text-gray-400">{{ $order->created_at->format('d M Y, H:i') }}</span>
                                    
                                    @if($order->status === 'selesai')
                                        <span class="ml-auto px-3 py-1 bg-green-50 text-green-700 text-xs font-bold rounded-lg border border-green-100 uppercase tracking-wide">
                                            Selesai
                                        </span>
                                    @elseif($order->status === 'dibatalkan')
                                        <span class="ml-auto px-3 py-1 bg-red-50 text-red-600 text-xs font-bold rounded-lg border border-red-100 uppercase tracking-wide">
                                            Dibatalkan
                                        </span>
                                    @endif
                                </div>

                                <div class="mb-5">
                                    <h3 class="text-lg font-extrabold text-gray-900 group-hover:text-[#c04b36] transition-colors mb-1">
                                        {{ $order->seller->store_name }}
                                    </h3>
                                    <p class="text-xs text-gray-400 font-semibold">{{ count($order->items) }} Item Pesanan</p>
                                </div>

                                <div class="space-y-2 border-t border-gray-50 pt-4">
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

                            <div class="lg:w-64 flex flex-col justify-between border-t lg:border-t-0 lg:border-l border-gray-50 pt-5 lg:pt-0 lg:pl-6">
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total Belanja</p>
                                    <p class="text-xl font-black text-gray-900 mb-4">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                                </div>
                                
                                <div class="flex flex-col gap-2">
                                    {{-- Logika Conditional Rendering Tombol --}}
                                    
                                    {{-- 1. Tombol Komplain --}}
                                    @if(in_array($order->status, ['diproses', 'selesai']))
                                        @if($order->hasActiveComplaint())
                                            <a href="{{ route('buyer.complaints.index') }}" 
                                               class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-orange-50 border border-orange-200 text-orange-700 hover:bg-orange-100 font-bold rounded-xl transition-all duration-200 text-xs shadow-sm mb-2">
                                                Lihat Status Komplain
                                            </a>
                                        @else
                                            <a href="{{ route('buyer.complaint.create', $order->seller_id) }}" 
                                               class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 hover:text-red-600 font-bold rounded-xl transition-all duration-200 text-xs shadow-sm mb-2">
                                                Ajukan Komplain
                                            </a>
                                        @endif
                                    @endif

                                    {{-- 2. Tombol Ulasan --}}
                                    @if($order->status === 'selesai')
                                        @if($order->hasReview())
                                            {{-- Sudah pernah dinilai / diulas --}}
                                            <span class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-emerald-50 border border-emerald-100 text-emerald-700 font-extrabold rounded-xl text-xs uppercase tracking-wider mb-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Ulasan Dikirim
                                            </span>
                                            <button type="button" 
                                                    @click="openReviewModal({{ $order->id }}, '{{ addslashes($order->seller->store_name) }}', {{ $order->review->rating }}, '{{ addslashes($order->review->comment) }}', true)"
                                                    class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-gray-50 border border-gray-200 text-gray-600 hover:bg-gray-100 font-bold rounded-xl transition-all duration-200 text-xs shadow-sm cursor-pointer focus:outline-none mb-2">
                                                Lihat Ulasan Saya
                                            </button>
                                        @else
                                            {{-- Belum dinilai, tampilkan tombol ulasan --}}
                                            <button type="button" 
                                                    @click="openReviewModal({{ $order->id }}, '{{ addslashes($order->seller->store_name) }}', 0, '', false)"
                                                    class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-3 bg-[#c04b36] hover:bg-[#a33d2b] text-white font-extrabold rounded-xl transition-all duration-200 text-sm shadow-md shadow-red-100 cursor-pointer focus:outline-none mb-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                                Beri Ulasan
                                            </button>
                                        @endif
                                    @endif
                                    
                                    <a href="{{ route('buyer.orders.show', $order->id) }}" 
                                       class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-gray-50 border border-gray-200 text-gray-600 hover:bg-gray-100 hover:text-gray-900 font-bold rounded-xl transition-all duration-200 text-xs shadow-sm">
                                        Lihat Invoice
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Card for Active Orders --}}
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 lg:p-8">
                        <div class="flex flex-col lg:flex-row gap-6">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-4">
                                    <span class="px-3 py-1 bg-gray-50 text-gray-500 text-xs font-bold rounded-lg border border-gray-100 uppercase tracking-wider">
                                        #{{ $order->id }}
                                    </span>
                                    <span class="text-sm font-medium text-gray-400">{{ $order->created_at->format('d M Y, H:i') }}</span>
                                    
                                    @if($order->status === 'menunggu_verifikasi')
                                        <span class="ml-auto px-3 py-1 bg-amber-50 text-amber-700 text-xs font-bold rounded-lg border border-amber-100 uppercase tracking-wide">
                                            Menunggu Verifikasi
                                        </span>
                                    @elseif($order->status === 'diproses')
                                        <span class="ml-auto px-3 py-1 bg-blue-50 text-blue-700 text-xs font-bold rounded-lg border border-blue-100 uppercase tracking-wide">
                                            Diproses
                                        </span>
                                    @elseif($order->status === 'siap_diambil')
                                        <span class="ml-auto px-3 py-1 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-lg border border-emerald-100 uppercase tracking-wide animate-pulse">
                                            Siap Diambil
                                        </span>
                                    @endif
                                </div>

                                <div class="mb-5">
                                    <h3 class="text-lg font-extrabold text-gray-900 mb-1">
                                        {{ $order->seller->store_name }}
                                    </h3>
                                    <p class="text-xs text-gray-400 font-semibold">{{ count($order->items) }} Item Pesanan</p>
                                </div>

                                <div class="space-y-2 border-t border-gray-50 pt-4">
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

                            <div class="lg:w-64 flex flex-col justify-between border-t lg:border-t-0 lg:border-l border-gray-50 pt-5 lg:pt-0 lg:pl-6">
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total Belanja</p>
                                    <p class="text-xl font-black text-gray-900 mb-4">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                                </div>
                                
                                <a href="{{ route('buyer.orders.show', $order->id) }}" 
                                   class="w-full inline-flex items-center justify-center px-4 py-3 bg-white border-2 border-gray-900 text-gray-900 hover:bg-gray-900 hover:text-white font-bold rounded-xl transition-colors text-sm">
                                    Lacak Pesanan
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @else
        {{-- Empty State --}}
        <div class="text-center py-20 bg-white rounded-[32px] border border-gray-100/80 shadow-sm max-w-2xl mx-auto px-6">
            <div class="w-32 h-32 mx-auto bg-red-50/50 rounded-full flex items-center justify-center mb-8 relative">
                <div class="absolute inset-0 bg-red-50 rounded-full animate-ping opacity-25"></div>
                <svg class="w-16 h-16 text-[#c04b36] relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    @if($tab === 'riwayat')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    @endif
                </svg>
            </div>
            
            @if($tab === 'riwayat')
                <h3 class="text-2xl font-black text-gray-900 mb-3">Belum ada riwayat pesanan</h3>
                <p class="text-gray-500 font-medium mb-8 max-w-md mx-auto">Anda belum memiliki riwayat pesanan. Mari cari makanan lezat dari toko di sekitar Anda!</p>
                <a href="{{ route('buyer.menu') }}" class="inline-flex px-8 py-4 bg-[#c04b36] hover:bg-[#a33d2b] text-white font-extrabold rounded-2xl shadow-lg shadow-red-100 hover:shadow-xl transition-all duration-300 text-sm">
                    Mari cari makanan!
                </a>
            @else
                <h3 class="text-2xl font-black text-gray-900 mb-3">Tidak ada pesanan aktif</h3>
                <p class="text-gray-500 font-medium mb-8 max-w-md mx-auto">Saat ini Anda tidak memiliki pesanan yang sedang diproses atau siap diambil.</p>
                <a href="{{ route('buyer.menu') }}" class="inline-flex px-8 py-4 bg-[#c04b36] hover:bg-[#a33d2b] text-white font-extrabold rounded-2xl shadow-lg shadow-red-100 hover:shadow-xl transition-all duration-300 text-sm">
                    Mulai Belanja
                </a>
            @endif
        </div>
    @endif

    {{-- Star Rating Modal --}}
    <div x-show="open" style="display: none;" class="fixed inset-0 z-[100] overflow-y-auto" role="dialog" aria-modal="true">
        {{-- Backdrop --}}
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm transition-opacity" @click="closeReviewModal()"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        {{-- Modal Panel --}}
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-100">
            
            <form action="{{ route('buyer.reviews.store') }}" method="POST">
                @csrf
                <input type="hidden" name="order_id" :value="orderId">
                
                {{-- Modal Header --}}
                <div class="px-6 pt-6 pb-4 flex justify-between items-center border-b border-gray-50">
                    <div>
                        <h3 class="text-lg font-black text-gray-900" x-text="isViewOnly ? 'Ulasan Kamu' : 'Beri Ulasan Pesanan'"></h3>
                        <p class="text-xs text-gray-400 font-semibold mt-1">Pesanan #<span x-text="orderId"></span> &bull; <span x-text="storeName"></span></p>
                    </div>
                    <button type="button" @click="closeReviewModal()" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-50 hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="px-6 py-6 space-y-6">
                    {{-- Star Rating --}}
                    <div class="text-center">
                        <label class="block text-sm font-bold text-gray-700 mb-3">Rating Bintang <span class="text-[#c04b36]" x-show="!isViewOnly">*</span></label>
                        
                        <div class="flex items-center justify-center gap-1.5">
                            <input type="hidden" name="rating" :value="rating">
                            
                            {{-- Bintang 1 s.d. 5 --}}
                            <template x-for="i in 5">
                                <button type="button" 
                                        :id="'star-rating-' + i"
                                        @click="if(!isViewOnly) rating = i"
                                        @mouseover="if(!isViewOnly) hoverRating = i"
                                        @mouseleave="if(!isViewOnly) hoverRating = 0"
                                        class="w-12 h-12 flex items-center justify-center transition-all duration-150 transform hover:scale-110 focus:outline-none"
                                        :class="{'cursor-default': isViewOnly, 'cursor-pointer': !isViewOnly}">
                                    <svg class="w-10 h-10 transition-colors duration-150" 
                                         :class="{
                                             'text-amber-400 fill-current': i <= (hoverRating || rating),
                                             'text-gray-200': i > (hoverRating || rating)
                                         }"
                                         viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </button>
                            </template>
                        </div>
                        
                        <p class="text-xs font-bold mt-2.5 uppercase tracking-wide"
                           :class="{
                               'text-red-500': rating == 1,
                               'text-orange-400': rating == 2,
                               'text-amber-500': rating == 3,
                               'text-teal-500': rating == 4,
                               'text-emerald-500': rating == 5,
                               'text-gray-400': rating == 0
                           }"
                           x-text="rating == 1 ? 'Sangat Buruk 😞' : 
                                   rating == 2 ? 'Buruk 🙁' : 
                                   rating == 3 ? 'Cukup Baik 😐' : 
                                   rating == 4 ? 'Puas & Enak! 🙂' : 
                                   rating == 5 ? 'Luar Biasa Sempurna! 🤩' : 'Klik bintang untuk menilai'"></p>
                    </div>

                    {{-- Comment Textarea --}}
                    <div>
                        <label for="comment" class="block text-sm font-bold text-gray-700 mb-2">
                            Ulasan Tertulis <span class="text-gray-400 font-medium text-xs">(Opsional)</span>
                        </label>
                        <textarea id="comment" 
                                  name="comment" 
                                  rows="4" 
                                  x-model="comment"
                                  :readonly="isViewOnly"
                                  class="w-full text-sm border border-gray-200 bg-gray-50 rounded-2xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-[#c04b36] font-medium transition-all"
                                  :class="{'bg-gray-50/50 text-gray-500 border-dashed cursor-default': isViewOnly}"
                                  placeholder="Ceritakan cita rasa surplus food atau pelayanan toko ini agar membantu calon pembeli lainnya..."></textarea>
                        
                        <div class="flex justify-between items-center mt-2 text-[10px] font-bold text-gray-400 uppercase tracking-wider" x-show="!isViewOnly">
                            <span>Maksimal 1000 karakter</span>
                            <span x-text="comment.length + '/1000'"></span>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row-reverse gap-2 rounded-b-3xl">
                    <button type="submit" 
                            x-show="!isViewOnly"
                            :disabled="rating === 0"
                            class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 bg-[#c04b36] hover:bg-[#a33d2b] disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed disabled:shadow-none text-sm font-extrabold text-white rounded-xl transition-all shadow-md shadow-red-100 cursor-pointer focus:outline-none">
                        Kirim Ulasan
                    </button>
                    <button type="button" 
                            @click="closeReviewModal()"
                            class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 bg-white border border-gray-200 text-sm font-extrabold text-gray-700 hover:bg-gray-50 rounded-xl transition-colors cursor-pointer"
                            x-text="isViewOnly ? 'Tutup' : 'Batal'">
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function reviewModalState() {
    return {
        open: false,
        orderId: null,
        storeName: '',
        rating: 0,
        hoverRating: 0,
        comment: '',
        isViewOnly: false,
        
        openReviewModal(orderId, storeName, rating = 0, comment = '', isViewOnly = false) {
            this.orderId = orderId;
            this.storeName = storeName;
            this.rating = rating;
            this.comment = comment || '';
            this.isViewOnly = isViewOnly;
            this.hoverRating = 0;
            this.open = true;
        },
        
        closeReviewModal() {
            this.open = false;
        }
    }
}
</script>
@endpush
@endsection
