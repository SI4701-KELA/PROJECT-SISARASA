<x-seller-layout>
    <div class="max-w-6xl mx-auto">
        <h2 class="text-3xl font-extrabold text-gray-900 mb-8 tracking-tight">Registration</h2>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-md shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
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

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Basic Info Card -->
                <div class="flex-grow bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                    <div class="flex items-center mb-6">
                        <svg class="h-5 w-5 text-terracotta mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-lg font-semibold text-terracotta">Basic Info</h3>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label for="store_name" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Store Name</label>
                            <input type="text" name="store_name" id="store_name" value="{{ old('store_name', $seller->store_name ?? '') }}" required placeholder="e.g. Green Leaf Organics" 
                                class="w-full bg-gray-200 border-transparent focus:border-transparent focus:ring-0 rounded-xl px-4 py-3 text-gray-700 placeholder-gray-400 font-medium">
                        </div>

                        <div>
                            <label for="address" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Full Address</label>
                            <textarea name="address" id="address" rows="3" required placeholder="123 Artisan Way, Culinary District, NY 10001" 
                                class="w-full bg-gray-200 border-transparent focus:border-transparent focus:ring-0 rounded-xl px-4 py-3 text-gray-700 placeholder-gray-400 font-medium resize-none">{{ old('address', $seller->address ?? '') }}</textarea>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-4">
                            <div class="flex-1">
                                <label for="latitude" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Latitude</label>
                                <input type="number" step="any" name="latitude" id="latitude" value="{{ old('latitude', $seller->latitude ?? '') }}" placeholder="e.g. 40.7128" 
                                    class="w-full bg-gray-200 border-transparent focus:border-transparent focus:ring-0 rounded-xl px-4 py-3 text-gray-700 placeholder-gray-400 font-medium">
                            </div>
                            <div class="flex-1">
                                <label for="longitude" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Longitude</label>
                                <input type="number" step="any" name="longitude" id="longitude" value="{{ old('longitude', $seller->longitude ?? '') }}" placeholder="e.g. -74.0060" 
                                    class="w-full bg-gray-200 border-transparent focus:border-transparent focus:ring-0 rounded-xl px-4 py-3 text-gray-700 placeholder-gray-400 font-medium">
                            </div>
                        </div>

                        <div>
                            <label for="store_photo" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Store Photo</label>
                            <div class="mt-1 flex items-center bg-gray-200 rounded-xl p-2">
                                @if(isset($seller) && $seller->store_photo)
                                    <span class="inline-block h-10 w-10 rounded-lg overflow-hidden bg-white mr-3 shadow-sm">
                                        <img src="{{ Storage::url($seller->store_photo) }}" alt="Foto Toko" class="h-full w-full object-cover">
                                    </span>
                                @endif
                                <input type="file" name="store_photo" id="store_photo" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-white file:text-terracotta hover:file:bg-gray-50 cursor-pointer">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hours Card -->
                <div class="w-full lg:w-80 flex flex-col gap-6">
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 flex-grow">
                        <div class="flex items-center mb-6">
                            <svg class="h-5 w-5 text-terracotta mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="text-lg font-semibold text-terracotta">Hours</h3>
                        </div>

                        <div class="space-y-6">
                            <div class="bg-gray-100 p-4 rounded-2xl">
                                <label class="flex items-center text-xs font-bold text-gray-500 mb-3">
                                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    Store Hours
                                </label>
                                <div class="flex items-center justify-between gap-2">
                                    <input type="time" name="open_time" id="open_time" value="{{ old('open_time', isset($seller) && $seller->open_time ? date('H:i', strtotime($seller->open_time)) : '') }}" required 
                                        class="w-[100px] bg-white border-transparent focus:border-terracotta focus:ring-terracotta rounded-xl px-2 py-2 text-sm text-gray-700 font-medium text-center shadow-sm">
                                    <span class="text-xs font-bold text-gray-400">to</span>
                                    <input type="time" name="close_time" id="close_time" value="{{ old('close_time', isset($seller) && $seller->close_time ? date('H:i', strtotime($seller->close_time)) : '') }}" required 
                                        class="w-[100px] bg-white border-transparent focus:border-terracotta focus:ring-terracotta rounded-xl px-2 py-2 text-sm text-gray-700 font-medium text-center shadow-sm">
                                </div>
                            </div>

                            <div class="bg-orange-50 p-4 rounded-2xl border border-orange-100">
                                <label class="flex items-center text-xs font-bold text-terracotta mb-1">
                                    <svg class="h-4 w-4 mr-1 text-terracotta" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    Discount Window
                                </label>
                                <p class="text-[10px] text-terracotta/70 mb-3 leading-tight">When surplus items are marked down automatically.</p>
                                
                                <div class="flex items-center justify-between gap-2">
                                    <input type="time" name="discount_time" id="discount_time" value="{{ old('discount_time', isset($seller) && $seller->discount_time ? date('H:i', strtotime($seller->discount_time)) : '') }}" required 
                                        class="w-[100px] bg-white border-transparent focus:border-terracotta focus:ring-terracotta rounded-xl px-2 py-2 text-sm text-gray-700 font-medium text-center shadow-sm">
                                    <span class="text-xs font-bold text-terracotta/50">to</span>
                                    <input type="time" value="{{ old('close_time', isset($seller) && $seller->close_time ? date('H:i', strtotime($seller->close_time)) : '') }}" disabled title="End time matches store closing time"
                                        class="w-[100px] bg-white/50 border-transparent rounded-xl px-2 py-2 text-sm text-gray-500 font-medium text-center shadow-sm cursor-not-allowed">
                                </div>
                            </div>
                        </div>

                        <div class="mt-8">
                            <button type="submit" class="w-full flex items-center justify-center py-3.5 px-4 border border-transparent rounded-full shadow-md text-sm font-bold text-white bg-terracotta hover:bg-[#a6402d] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-terracotta transition-all duration-200 hover:scale-[1.02]">
                                Complete Registration &rarr;
                            </button>
                            <p class="text-[10px] text-center text-gray-400 mt-4 px-4 leading-tight font-medium">By completing registration you agree to our creator standards and quality guidelines.</p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-seller-layout>
