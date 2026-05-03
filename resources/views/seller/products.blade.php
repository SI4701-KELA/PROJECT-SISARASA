<x-seller-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h2 class="text-3xl font-extrabold text-gray-900 mb-8 tracking-tight">Manajemen Produk Harian</h2>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-md shadow-sm">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-md shadow-sm">
                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-md shadow-sm">
                <ul class="list-disc list-inside text-sm text-red-600 font-medium">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Blok Atas: Form Input -->
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 mb-10">
            <div class="flex items-center mb-6">
                <svg class="h-6 w-6 text-terracotta mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <h3 class="text-xl font-semibold text-terracotta">Tambah Produk Baru</h3>
            </div>

            <form action="{{ route('seller.product.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Nama Makanan</label>
                        <input type="text" name="name" id="name" required class="w-full bg-gray-200 border-transparent focus:border-transparent focus:ring-0 rounded-xl px-4 py-3 text-gray-700 font-medium">
                    </div>

                    <div>
                        <label for="category_id" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Kategori</label>
                        <select name="category_id" id="category_id" required class="w-full bg-gray-200 border-transparent focus:border-transparent focus:ring-0 rounded-xl px-4 py-3 text-gray-700 font-medium">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Deskripsi</label>
                        <textarea name="description" id="description" rows="3" class="w-full bg-gray-200 border-transparent focus:border-transparent focus:ring-0 rounded-xl px-4 py-3 text-gray-700 font-medium resize-none"></textarea>
                    </div>

                    <div>
                        <label for="base_price" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Harga Normal (Rp)</label>
                        <input type="number" name="base_price" id="base_price" min="0" required class="w-full bg-gray-200 border-transparent focus:border-transparent focus:ring-0 rounded-xl px-4 py-3 text-gray-700 font-medium">
                    </div>

                    <div>
                        <label for="discount_price" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Harga Promo Sisa Rasa (Rp)</label>
                        <input type="number" name="discount_price" id="discount_price" min="0" required class="w-full bg-gray-200 border-transparent focus:border-transparent focus:ring-0 rounded-xl px-4 py-3 text-gray-700 font-medium">
                    </div>

                    <div>
                        <label for="qty_reg" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Jumlah Porsi Disiapkan</label>
                        <input type="number" name="qty_reg" id="qty_reg" min="1" required class="w-full bg-gray-200 border-transparent focus:border-transparent focus:ring-0 rounded-xl px-4 py-3 text-gray-700 font-medium">
                    </div>

                    <div>
                        <label for="image" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Foto Produk (Max 2MB)</label>
                        <input type="file" name="image" id="image" accept="image/*" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-white file:text-terracotta hover:file:bg-gray-50 cursor-pointer bg-gray-200 rounded-xl p-2">
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <button type="submit" class="py-3 px-8 border border-transparent rounded-full shadow-md text-sm font-bold text-white bg-terracotta hover:bg-[#a6402d] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-terracotta transition-all duration-200 hover:scale-[1.02]">
                        Simpan Produk
                    </button>
                </div>
            </form>
        </div>

        <!-- Blok Bawah: Katalog Preview Grid -->
        <h3 class="text-2xl font-bold text-gray-900 mb-6 tracking-tight">Katalog Produk</h3>
        
        @if($products->isEmpty())
            <div class="text-center py-10 bg-gray-50 rounded-2xl border border-dashed border-gray-300">
                <p class="text-gray-500 font-medium">Belum ada produk yang ditambahkan.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($products as $product)
                    <div x-data="{ editOpen: false }" class="bg-white rounded-3xl overflow-hidden shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-300">
                        <div class="h-48 w-full bg-gray-200 relative">
                            <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                        </div>
                        <div class="p-5">
                            <span class="inline-block px-2 py-1 bg-orange-100 text-terracotta text-[10px] font-bold uppercase tracking-wider rounded-md mb-2">
                                {{ $product->category->name ?? 'Uncategorized' }}
                            </span>
                            <h4 class="text-lg font-bold text-gray-900 mb-1 truncate">{{ $product->name }}</h4>
                            @if($product->discount && $product->discount->is_active)
                                <div class="mb-3">
                                    <p class="text-gray-400 font-medium text-sm line-through decoration-red-500">Rp {{ number_format($product->base_price, 0, ',', '.') }}</p>
                                    <p class="text-terracotta font-extrabold text-xl">Rp {{ number_format($product->discount->discount_price, 0, ',', '.') }}</p>
                                </div>
                            @else
                                <p class="text-terracotta font-extrabold text-xl mb-3">Rp {{ number_format($product->base_price, 0, ',', '.') }}</p>
                            @endif
                            
                            <div class="flex items-center justify-between border-t border-gray-100 pt-3">
                                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Sisa Porsi</span>
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 text-sm font-bold rounded-full">
                                    {{ $product->stock->qty_reg ?? 0 }}
                                </span>
                            </div>

                            <div class="flex gap-2 mt-4">
                                <button @click="editOpen = true" type="button" class="flex-1 bg-teal-50 hover:bg-teal-100 text-teal-600 font-bold py-2 px-3 rounded-xl transition-colors text-sm text-center">
                                    Edit Makanan
                                </button>
                                <form action="{{ route('product.destroy', $product->id) }}" method="POST" class="flex-none" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus Makanan" class="bg-red-50 hover:bg-red-100 text-red-600 font-bold py-2 px-3 rounded-xl transition-colors flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </div>

                            <!-- Edit Modal -->
                            <div x-show="editOpen" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
                                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                    <div x-show="editOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="editOpen = false"></div>
                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                    <div x-show="editOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                            <div class="flex justify-between items-center mb-5">
                                                <h3 class="text-xl leading-6 font-bold text-gray-900" id="modal-title">Edit Makanan: {{ $product->name }}</h3>
                                                <button @click="editOpen = false" type="button" class="text-gray-400 hover:text-gray-500">
                                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                </button>
                                            </div>
                                            <form action="{{ route('product.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                @method('PUT')
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Nama Makanan</label>
                                                        <input type="text" name="name" value="{{ $product->name }}" required class="w-full bg-gray-100 border-transparent focus:border-transparent focus:ring-0 rounded-xl px-3 py-2 text-gray-700 font-medium">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Kategori</label>
                                                        <select name="category_id" required class="w-full bg-gray-100 border-transparent focus:border-transparent focus:ring-0 rounded-xl px-3 py-2 text-gray-700 font-medium">
                                                            @foreach($categories as $category)
                                                                <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Deskripsi</label>
                                                        <textarea name="description" rows="2" class="w-full bg-gray-100 border-transparent focus:border-transparent focus:ring-0 rounded-xl px-3 py-2 text-gray-700 font-medium resize-none">{{ $product->description }}</textarea>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Harga Normal</label>
                                                        <input type="number" name="base_price" value="{{ $product->base_price }}" min="0" required class="w-full bg-gray-100 border-transparent focus:border-transparent focus:ring-0 rounded-xl px-3 py-2 text-gray-700 font-medium">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Harga Promo Sisa Rasa</label>
                                                        <input type="number" name="discount_price" value="{{ $product->discount ? $product->discount->discount_price : 0 }}" min="0" required class="w-full bg-gray-100 border-transparent focus:border-transparent focus:ring-0 rounded-xl px-3 py-2 text-gray-700 font-medium">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Porsi Disiapkan</label>
                                                        <input type="number" name="qty_reg" value="{{ $product->stock ? $product->stock->qty_reg : 0 }}" min="1" required class="w-full bg-gray-100 border-transparent focus:border-transparent focus:ring-0 rounded-xl px-3 py-2 text-gray-700 font-medium">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Ganti Foto (Opsional)</label>
                                                        <input type="file" name="image" accept="image/*" class="w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-white file:text-teal-600 hover:file:bg-gray-50 cursor-pointer bg-gray-100 rounded-xl p-1">
                                                    </div>
                                                </div>
                                                <div class="mt-6 flex justify-end gap-3">
                                                    <button type="button" @click="editOpen = false" class="py-2 px-4 bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold rounded-xl transition-colors">Batal</button>
                                                    <button type="submit" class="py-2 px-6 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl shadow-md transition-all duration-200 hover:scale-[1.02]">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <form action="{{ route('seller.product.toggle-discount', $product->id) }}" method="POST" class="mt-4">
                                @csrf
                                @method('PATCH')
                                @if($product->discount && $product->discount->is_active)
                                    <button type="submit" class="w-full bg-[#c04b36] hover:bg-red-800 text-white font-extrabold py-2 px-4 rounded-xl shadow-[0_0_15px_rgba(192,75,54,0.6)] animate-[pulse_2s_ease-in-out_infinite] transition-all">
                                        HENTIKAN DISKON SISA RASA
                                    </button>
                                @else
                                    <button type="submit" class="w-full bg-transparent border-2 border-teal-500 text-teal-600 hover:bg-teal-50 font-bold py-2 px-4 rounded-xl transition-colors">
                                        AKTIFKAN DISKON SISA RASA
                                    </button>
                                @endif
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-seller-layout>
