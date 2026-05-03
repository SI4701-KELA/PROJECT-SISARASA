<x-buyer-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h2 class="text-3xl font-extrabold text-gray-900 mb-6 tracking-tight">Katalog Menu Sisa Rasa</h2>

        <!-- Komponen Navigator Kategori -->
        <div class="flex overflow-x-auto pb-4 mb-6 gap-3 scrollbar-hide">
            <a href="{{ route('buyer.menu') }}" 
               class="whitespace-nowrap px-4 py-2 rounded-full text-sm transition-colors {{ is_null($categoryId) ? 'bg-[#c04b36] text-white font-bold' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                Semua Makanan
            </a>
            @foreach($categories as $category)
                <a href="{{ route('buyer.menu', ['category_id' => $category->id]) }}" 
                   class="whitespace-nowrap px-4 py-2 rounded-full text-sm transition-colors {{ $categoryId == $category->id ? 'bg-[#c04b36] text-white font-bold' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>

        <!-- Grid Catalog -->
        @if($products->isEmpty())
            <div class="text-center py-12 bg-white rounded-3xl border border-dashed border-gray-300">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900">Tidak ada produk</h3>
                <p class="mt-1 text-sm text-gray-500">Belum ada makanan untuk kategori ini.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($products as $product)
                    <div class="bg-white rounded-3xl overflow-hidden shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-300 relative group">
                        <div class="h-48 w-full bg-gray-200 relative overflow-hidden">
                            @if($product->image)
                                <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400">No Image</div>
                            @endif
                            <div class="absolute top-3 left-3">
                                <span class="px-2 py-1 bg-white/90 backdrop-blur-sm text-gray-800 text-[10px] font-bold uppercase tracking-wider rounded-md shadow-sm">
                                    {{ $product->category->name ?? 'Uncategorized' }}
                                </span>
                            </div>
                        </div>
                        <div class="p-5">
                            <h4 class="text-lg font-bold text-gray-900 mb-1 truncate">{{ $product->name }}</h4>
                            <p class="text-sm text-gray-500 mb-3 flex items-center">
                                <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                {{ $product->seller->store_name ?? 'Toko Tidak Diketahui' }}
                            </p>
                            
                            <div class="flex items-end justify-between mt-4">
                                <div class="flex flex-col justify-end">
                                    @if(isset($product->discounts[0]) && $product->discounts[0]->is_active)
                                        <div class="mb-1">
                                            <span class="inline-block px-2 py-0.5 bg-red-50 border border-red-200 text-red-600 text-[10px] font-bold uppercase tracking-wider rounded-full">PROMO SISA RASA</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <p class="text-gray-400 text-xs font-medium line-through">Rp {{ number_format($product->base_price, 0, ',', '.') }}</p>
                                            <p class="text-red-500 font-extrabold text-sm">Rp {{ number_format($product->discounts[0]->discount_price, 0, ',', '.') }}</p>
                                        </div>
                                    @else
                                        <p class="text-gray-600 font-bold text-sm mt-0.5">Rp {{ number_format($product->base_price, 0, ',', '.') }}</p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">Sisa</span>
                                    <span class="px-2 py-1 bg-orange-100 text-terracotta text-xs font-bold rounded-full">
                                        {{ $product->stock->qty_reg ?? 0 }} Porsi
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-buyer-layout>
