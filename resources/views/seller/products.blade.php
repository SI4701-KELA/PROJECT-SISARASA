@extends('layouts.seller')

@section('title', 'Katalog Produk')

@section('content')
<div class="max-w-7xl" x-data="{ addOpen: false }">
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl shadow-sm font-medium text-sm flex items-center gap-3">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl shadow-sm font-medium text-sm">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl shadow-sm font-medium text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex justify-between items-start mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight mb-2">Katalog Produk</h1>
            <p class="text-sm text-gray-500 font-medium">Kelola makanan yang tersedia dan surplus untuk didiskon sesuai jam.</p>
        </div>
        <button @click="addOpen = true" class="bg-terracotta hover:bg-[#a6402d] text-white font-bold py-2.5 px-6 rounded-full shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-terracotta text-sm flex items-center gap-2">
            <span>+</span> Tambah Produk
        </button>
    </div>

    @if($products->isEmpty())
        <div class="text-center py-16 bg-white rounded-[24px] border border-gray-100 shadow-sm">
            <svg class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <p class="text-gray-500 font-medium">Belum ada produk yang ditambahkan.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($products as $product)
                @php
                    $isActive = $product->discount && $product->discount->is_active;
                @endphp
                <div x-data="{ editOpen: false }" class="bg-white rounded-[24px] overflow-hidden shadow-sm border border-gray-100 flex flex-col hover:shadow-md transition-shadow h-full">
                    
                    {{-- Image Area --}}
                    <div class="h-48 w-full bg-teal-600 relative overflow-hidden flex-shrink-0">
                        @if($product->image)
                            <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                        @endif
                        
                        {{-- Top Pills --}}
                        <div class="absolute top-4 left-4 right-4 flex justify-between items-start">
                            <span class="px-3 py-1 bg-gray-800/80 backdrop-blur-md text-white text-[10px] font-bold rounded-full border border-gray-600/50">
                                {{ $product->category->name ?? 'Uncategorized' }}
                            </span>
                            @if($isActive)
                                <span class="px-3 py-1 bg-red-500 text-white text-[10px] font-bold rounded-full shadow-sm">
                                    Diskon!
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Content Area --}}
                    <div class="p-6 flex flex-col flex-1">
                        <div class="mb-4 flex-1">
                            <h4 class="text-lg font-bold text-gray-900 leading-tight mb-1">{{ $product->name }}</h4>
                            <p class="text-[11px] text-gray-500 line-clamp-2 leading-relaxed">{{ $product->description }}</p>
                        </div>
                        
                        <div class="flex justify-between items-end mb-4">
                            <div>
                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1">HARGA NORMAL</p>
                                <p class="text-sm font-bold {{ $isActive ? 'text-gray-400 line-through' : 'text-gray-700' }}">Rp {{ number_format($product->base_price, 0, ',', '.') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[9px] font-bold text-green-500 uppercase tracking-widest mb-1">HARGA DISKON</p>
                                <p class="text-lg font-black text-green-500 leading-none">Rp {{ number_format($product->discount ? $product->discount->discount_price : 0, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center mb-5">
                            <span class="text-xs text-gray-500 font-medium">Stok: {{ $product->stock->qty_reg ?? 0 }}</span>
                            <div class="flex items-center gap-3 text-xs font-bold">
                                <button @click="editOpen = true" class="text-blue-600 hover:text-blue-800 transition-colors">Edit</button>
                                <form action="{{ route('product.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 transition-colors">Hapus</button>
                                </form>
                            </div>
                        </div>

                        {{-- Toggle Diskon Button --}}
                        <form action="{{ route('seller.product.toggle-discount', $product->id) }}" method="POST" class="mt-auto">
                            @csrf @method('PATCH')
                            @if($isActive)
                                <button type="submit" class="w-full bg-red-50 text-red-600 hover:bg-red-100 font-bold py-2.5 px-4 rounded-xl transition-colors text-sm text-center border border-red-100">
                                    Matikan Diskon
                                </button>
                            @else
                                <button type="submit" class="w-full bg-green-50 text-green-600 hover:bg-green-100 font-bold py-2.5 px-4 rounded-xl transition-colors text-sm text-center border border-green-100">
                                    Aktifkan Diskon
                                </button>
                            @endif
                        </form>
                    </div>

                    {{-- Edit Modal --}}
                    <div x-show="editOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                            <div x-show="editOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" @click="editOpen = false"></div>

                            <div x-show="editOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full relative z-50">
                                <div class="px-6 py-6 border-b border-gray-100 flex justify-between items-center">
                                    <h3 class="text-xl font-bold text-gray-900">Edit Produk</h3>
                                    <button @click="editOpen = false" class="text-gray-400 hover:text-gray-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                </div>
                                <form action="{{ route('product.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="p-6">
                                    @csrf @method('PUT')
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nama Makanan</label>
                                            <input type="text" name="name" value="{{ $product->name }}" required class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2 text-sm focus:border-terracotta focus:ring-1 focus:ring-terracotta">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Kategori</label>
                                            <select name="category_id" required class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2 text-sm focus:border-terracotta focus:ring-1 focus:ring-terracotta">
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Deskripsi</label>
                                            <textarea name="description" rows="2" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2 text-sm focus:border-terracotta focus:ring-1 focus:ring-terracotta resize-none">{{ $product->description }}</textarea>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Harga Normal</label>
                                                <input type="number" name="base_price" value="{{ $product->base_price }}" min="0" required class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2 text-sm focus:border-terracotta focus:ring-1 focus:ring-terracotta">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Harga Diskon</label>
                                                <input type="number" name="discount_price" value="{{ $product->discount ? $product->discount->discount_price : 0 }}" min="0" required class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2 text-sm focus:border-terracotta focus:ring-1 focus:ring-terracotta">
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Stok</label>
                                                <input type="number" name="qty_reg" value="{{ $product->stock ? $product->stock->qty_reg : 0 }}" min="1" required class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2 text-sm focus:border-terracotta focus:ring-1 focus:ring-terracotta">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Ganti Foto (Opsional)</label>
                                                <input type="file" name="image" accept="image/*" class="w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:bg-gray-100 cursor-pointer mt-1">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-8 flex justify-end gap-3">
                                        <button type="button" @click="editOpen = false" class="py-2.5 px-5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-sm transition-colors">Batal</button>
                                        <button type="submit" class="py-2.5 px-6 bg-terracotta hover:bg-[#a6402d] text-white font-bold rounded-xl text-sm shadow-sm transition-colors">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Add Product Modal --}}
    <div x-show="addOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div x-show="addOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" @click="addOpen = false"></div>

            <div x-show="addOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full relative z-50">
                <div class="px-6 py-6 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-gray-900">Tambah Produk Baru</h3>
                    <button @click="addOpen = false" class="text-gray-400 hover:text-gray-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <form action="{{ route('seller.product.store') }}" method="POST" enctype="multipart/form-data" class="p-6">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nama Makanan</label>
                            <input type="text" name="name" required class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2 text-sm focus:border-terracotta focus:ring-1 focus:ring-terracotta">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Kategori</label>
                            <select name="category_id" required class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2 text-sm focus:border-terracotta focus:ring-1 focus:ring-terracotta">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Deskripsi</label>
                            <textarea name="description" rows="2" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2 text-sm focus:border-terracotta focus:ring-1 focus:ring-terracotta resize-none"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Harga Normal</label>
                                <input type="number" name="base_price" min="0" required class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2 text-sm focus:border-terracotta focus:ring-1 focus:ring-terracotta">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Harga Diskon</label>
                                <input type="number" name="discount_price" min="0" required class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2 text-sm focus:border-terracotta focus:ring-1 focus:ring-terracotta">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Stok Awal</label>
                                <input type="number" name="qty_reg" min="1" required class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2 text-sm focus:border-terracotta focus:ring-1 focus:ring-terracotta">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Foto Produk</label>
                                <input type="file" name="image" accept="image/*" required class="w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:bg-gray-100 cursor-pointer mt-1">
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end gap-3">
                        <button type="button" @click="addOpen = false" class="py-2.5 px-5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-sm transition-colors">Batal</button>
                        <button type="submit" class="py-2.5 px-6 bg-terracotta hover:bg-[#a6402d] text-white font-bold rounded-xl text-sm shadow-sm transition-colors">Simpan Produk</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
