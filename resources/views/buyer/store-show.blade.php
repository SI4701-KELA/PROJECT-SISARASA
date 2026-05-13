@extends('layouts.buyer')

@section('title', $seller->store_name ?? 'Detail Toko')

@section('content')
<div class="max-w-3xl mx-auto" x-data="{ reportModalOpen: false }">

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
    @if ($errors->any())
        <div class="mb-6 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm font-medium shadow-sm">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Back Button --}}
    <a href="{{ url()->previous() }}"
       class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-500 hover:text-gray-800 mb-6 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali
    </a>

    {{-- Store Hero Card --}}
    <div class="bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-sm mb-6">

        {{-- Cover / Header --}}
        <div class="relative bg-gradient-to-br from-[#2aab7f] to-[#1d8a63] px-6 py-8">

            {{-- Store Photo --}}
            <div class="flex items-center gap-5">
                @if($seller->store_photo)
                    <img src="{{ asset('storage/' . $seller->store_photo) }}"
                         alt="{{ $seller->store_name }}"
                         class="w-20 h-20 rounded-2xl object-cover border-4 border-white/30 shadow-lg shrink-0">
                @else
                    <div class="w-20 h-20 rounded-2xl bg-white/20 border-4 border-white/30 flex items-center justify-center text-white font-black text-3xl shadow-lg shrink-0">
                        {{ strtoupper(substr($seller->store_name ?? 'T', 0, 1)) }}
                    </div>
                @endif

                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl font-black text-white leading-tight truncate">
                        {{ $seller->store_name ?? 'Nama Toko' }}
                    </h1>
                    <span class="mt-1 inline-flex items-center gap-1 px-2.5 py-1 bg-white/20 rounded-full text-xs font-bold text-white">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Terverifikasi
                    </span>
                </div>
            </div>
        </div>

        {{-- Store Info Body --}}
        <div class="px-6 py-5 space-y-4">

            {{-- Description --}}
            @if($seller->description ?? false)
                <div>
                    <p class="text-xs font-bold text-gray-400 tracking-widest uppercase mb-1.5">Tentang Toko</p>
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $seller->description }}</p>
                </div>
            @else
                <div>
                    <p class="text-xs font-bold text-gray-400 tracking-widest uppercase mb-1.5">Tentang Toko</p>
                    <p class="text-sm text-gray-400 italic">Belum ada deskripsi untuk toko ini.</p>
                </div>
            @endif

            <hr class="border-gray-100">

            {{-- Address --}}
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl bg-orange-50 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 tracking-widest uppercase mb-0.5">Alamat</p>
                    <p class="text-sm text-gray-700 font-medium">{{ $seller->address ?? 'Alamat belum tersedia' }}</p>
                </div>
            </div>

            {{-- Jam Buka --}}
            @if($seller->open_time || $seller->close_time)
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl bg-blue-50 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 tracking-widest uppercase mb-0.5">Jam Operasional</p>
                    <p class="text-sm text-gray-700 font-medium">
                        {{ $seller->open_time ? date('H:i', strtotime($seller->open_time)) : '--:--' }}
                        &ndash;
                        {{ $seller->close_time ? date('H:i', strtotime($seller->close_time)) : '--:--' }}
                    </p>
                </div>
            </div>
            @endif

            {{-- Discount Time --}}
            @if($seller->discount_time)
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl bg-red-50 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-[#c04b36]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 tracking-widest uppercase mb-0.5">Mulai Diskon Harga Sisa</p>
                    <p class="text-sm text-[#c04b36] font-bold">
                        {{ date('H:i', strtotime($seller->discount_time)) }} WIB
                    </p>
                </div>
            </div>
            @endif

        </div>
        {{-- PBI 28 + PBI 20: Tombol Aksi --}}
        <div class="px-6 pb-6 pt-2">
            <div class="pt-4 border-t border-gray-100 flex justify-end gap-2">
                {{-- PBI-20: Tombol Ajukan Komplain --}}
                @php
                    $hasActiveComplaint = \App\Models\Complaint::where('seller_id', $seller->id)
                        ->where('buyer_id', auth()->id())
                        ->whereIn('status_tiket', ['Open', 'Sedang Diproses'])
                        ->exists();
                @endphp
                @if(!$hasActiveComplaint)
                <a href="{{ route('buyer.complaint.create', $seller) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-orange-600 bg-orange-50 hover:bg-orange-100 rounded-lg transition-colors border border-orange-100 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Ajukan Komplain
                </a>
                @else
                <a href="{{ route('buyer.complaints.index') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-amber-600 bg-amber-50 rounded-lg border border-amber-100">
                    ⏳ Tiket Aktif
                </a>
                @endif
                {{-- PBI-28: Laporkan Toko --}}
                <button @click="reportModalOpen = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors border border-red-100 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                    </svg>
                    Laporkan Toko Ini
                </button>
            </div>
        </div>
    </div>


    {{-- Products Section --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Section Header --}}
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-gray-900">Katalog Produk</h2>
                <p class="text-xs text-gray-400 mt-0.5">{{ $seller->products->count() }} produk tersedia</p>
            </div>
            <span class="px-3 py-1 bg-[#2aab7f]/10 text-[#2aab7f] text-xs font-bold rounded-full">
                {{ $seller->products->count() }} Menu
            </span>
        </div>

        @if($seller->products->isEmpty())
            <div class="py-16 text-center">
                <div class="w-14 h-14 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                </div>
                <p class="text-sm text-gray-400 font-medium">Belum ada produk di toko ini.</p>
            </div>
        @else
            <div class="divide-y divide-gray-50">
                @foreach($seller->products as $product)
                @php
                    $activeDiscount = $product->discounts->where('is_active', true)->first();
                @endphp
                <div class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 transition-colors">

                    {{-- Product Image --}}
                    <div class="w-16 h-16 rounded-xl overflow-hidden bg-gray-100 shrink-0">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}"
                                 alt="{{ $product->name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif
                    </div>

                    {{-- Product Info --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-800 truncate">{{ $product->name }}</p>
                        @if($product->description ?? false)
                            <p class="text-xs text-gray-400 truncate mt-0.5">{{ $product->description }}</p>
                        @endif
                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                            @if($activeDiscount)
                                <span class="text-xs text-gray-400 line-through">
                                    Rp {{ number_format($product->base_price, 0, ',', '.') }}
                                </span>
                                <span class="text-sm font-black text-[#c04b36]">
                                    Rp {{ number_format($activeDiscount->discount_price, 0, ',', '.') }}
                                </span>
                                <span class="text-[10px] font-bold bg-[#c04b36]/10 text-[#c04b36] px-2 py-0.5 rounded-full">
                                    PROMO
                                </span>
                            @else
                                <span class="text-sm font-bold text-gray-700">
                                    Rp {{ number_format($product->base_price, 0, ',', '.') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Stock Badge --}}
                    @if($product->stock ?? false)
                        <span class="shrink-0 text-xs font-semibold text-gray-400 bg-gray-50 border border-gray-100 px-2 py-1 rounded-lg">
                            Stok: {{ $product->stock->quantity ?? 0 }}
                        </span>
                    @endif

                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- PBI 28: Modal Pelaporan Toko --}}
    <div x-show="reportModalOpen" style="display: none;" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="reportModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm transition-opacity" @click="reportModalOpen = false" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="reportModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('buyer.reports.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="seller_id" value="{{ $seller->id }}">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-black text-gray-900" id="modal-title">Laporkan Toko</h3>
                                <div class="mt-2 text-sm text-gray-500 font-medium">
                                    <p>Tindakan penipuan, fiktif, atau kualitas buruk akan kami tindaklanjuti. Laporan Anda bersifat rahasia.</p>
                                </div>
                                
                                <div class="mt-5 space-y-4">
                                    <div>
                                        <label for="kategori" class="block text-sm font-bold text-gray-700">Kategori Laporan <span class="text-red-500">*</span></label>
                                        <select id="kategori" name="kategori" required class="mt-1.5 block w-full pl-3 pr-10 py-2.5 text-sm border border-gray-200 focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-red-500 rounded-xl bg-gray-50 font-medium">
                                            <option value="" disabled selected>-- Pilih Kategori --</option>
                                            <option value="Toko Fiktif">Toko Fiktif</option>
                                            <option value="Penipuan">Penipuan</option>
                                            <option value="Kualitas Makanan Buruk">Kualitas Makanan Buruk</option>
                                            <option value="Lainnya">Lainnya</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="deskripsi" class="block text-sm font-bold text-gray-700">Deskripsi Kejadian <span class="text-red-500">*</span></label>
                                        <textarea id="deskripsi" name="deskripsi" rows="3" required class="mt-1.5 block w-full text-sm border border-gray-200 bg-gray-50 rounded-xl py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-red-500 font-medium" placeholder="Ceritakan kronologi kejadian secara detail..."></textarea>
                                    </div>
                                    <div>
                                        <label for="foto_bukti" class="block text-sm font-bold text-gray-700">Foto Bukti <span class="text-gray-400 font-medium text-xs">(Opsional)</span></label>
                                        <div class="mt-1.5">
                                            <input type="file" id="foto_bukti" name="foto_bukti" accept=".jpg,.jpeg,.png" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-red-50 file:text-red-600 hover:file:bg-red-100 border border-gray-200 rounded-xl bg-gray-50 p-1 cursor-pointer">
                                        </div>
                                        <p class="text-[10px] font-bold text-gray-400 mt-2 uppercase tracking-wider">Maksimal 2MB (JPG, PNG).</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-4 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-100">
                        <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-5 py-2.5 bg-red-600 text-sm font-bold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto transition-colors">
                            Kirim Laporan
                        </button>
                        <button type="button" @click="reportModalOpen = false" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-200 shadow-sm px-5 py-2.5 bg-white text-sm font-bold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-100 sm:mt-0 sm:ml-3 sm:w-auto transition-colors">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
