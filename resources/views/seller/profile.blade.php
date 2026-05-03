@extends('layouts.seller')

@section('title', 'Profile Toko')

@section('content')
<div class="max-w-4xl">
    <h1 class="text-3xl font-black text-gray-900 tracking-tight mb-8">Profile Toko</h1>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl shadow-sm font-medium text-sm flex items-center gap-3">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
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

    <div class="bg-white rounded-[24px] border border-gray-100 shadow-sm p-10 mb-8">
        <div class="mb-8 border-b border-gray-100 pb-6">
            <h2 class="text-xl font-bold text-terracotta mb-1">Profil Toko Anda</h2>
            <p class="text-sm text-gray-500 font-medium">Perbarui nama toko, lokasi, dan jam operasional diskon otomatis.</p>
        </div>

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Foto Profil Toko --}}
            <div>
                <label for="store_photo" class="block text-sm font-semibold text-gray-700 mb-3">Foto Profil Toko (Opsional)</label>
                <div class="flex items-center gap-4">
                    @if(isset($seller) && $seller->store_photo)
                        <img src="{{ Storage::url($seller->store_photo) }}" alt="Foto Toko" class="w-16 h-16 rounded-full object-cover shadow-sm border border-gray-100">
                    @else
                        <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 border border-gray-200 border-dashed">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    @endif
                    <div class="flex-1">
                        <input type="file" name="store_photo" id="store_photo" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-terracotta hover:file:bg-red-100 cursor-pointer transition-colors">
                    </div>
                </div>
            </div>

            {{-- Nama Toko --}}
            <div>
                <label for="store_name" class="block text-sm font-semibold text-gray-700 mb-2">Nama Toko</label>
                <input type="text" name="store_name" id="store_name" value="{{ old('store_name', $seller->store_name ?? '') }}" required
                    class="w-full bg-white border border-gray-200 focus:border-terracotta focus:ring-1 focus:ring-terracotta rounded-xl px-4 py-3 text-sm text-gray-800 placeholder-gray-400 font-medium transition-all">
            </div>

            {{-- Alamat Lengkap --}}
            <div>
                <label for="address" class="block text-sm font-semibold text-gray-700 mb-2">Alamat Lengkap</label>
                <textarea name="address" id="address" rows="2" required
                    class="w-full bg-white border border-gray-200 focus:border-terracotta focus:ring-1 focus:ring-terracotta rounded-xl px-4 py-3 text-sm text-gray-800 placeholder-gray-400 font-medium transition-all resize-none">{{ old('address', $seller->address ?? '') }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Latitude --}}
                <div>
                    <label for="latitude" class="block text-sm font-semibold text-gray-700 mb-2">Latitude</label>
                    <input type="number" step="any" name="latitude" id="latitude" value="{{ old('latitude', $seller->latitude ?? '') }}"
                        class="w-full bg-white border border-gray-200 focus:border-terracotta focus:ring-1 focus:ring-terracotta rounded-xl px-4 py-3 text-sm text-gray-800 placeholder-gray-400 font-medium transition-all">
                </div>
                {{-- Longitude --}}
                <div>
                    <label for="longitude" class="block text-sm font-semibold text-gray-700 mb-2">Longitude</label>
                    <input type="number" step="any" name="longitude" id="longitude" value="{{ old('longitude', $seller->longitude ?? '') }}"
                        class="w-full bg-white border border-gray-200 focus:border-terracotta focus:ring-1 focus:ring-terracotta rounded-xl px-4 py-3 text-sm text-gray-800 placeholder-gray-400 font-medium transition-all">
                </div>
            </div>

            <div class="border-t border-gray-100 pt-6 mt-6 space-y-6">
                {{-- Jam Buka --}}
                <div>
                    <label for="open_time" class="block text-sm font-semibold text-gray-700 mb-2">Jam Buka</label>
                    <div class="relative">
                        <input type="time" name="open_time" id="open_time" value="{{ old('open_time', isset($seller) && $seller->open_time ? date('H:i', strtotime($seller->open_time)) : '') }}" required
                            class="w-full bg-white border border-gray-200 focus:border-terracotta focus:ring-1 focus:ring-terracotta rounded-xl px-4 py-3 text-sm text-gray-800 placeholder-gray-400 font-medium transition-all">
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                </div>

                {{-- Jam Aktif Diskon --}}
                <div>
                    <label for="discount_time" class="block text-sm font-semibold text-gray-700 mb-2">Jam Aktif Diskon</label>
                    <div class="relative">
                        <input type="time" name="discount_time" id="discount_time" value="{{ old('discount_time', isset($seller) && $seller->discount_time ? date('H:i', strtotime($seller->discount_time)) : '') }}" required
                            class="w-full bg-red-50 border border-red-100 focus:border-terracotta focus:ring-1 focus:ring-terracotta rounded-xl px-4 py-3 text-sm text-gray-800 placeholder-gray-400 font-medium transition-all">
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-terracotta">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                </div>

                {{-- Jam Tutup --}}
                <div>
                    <label for="close_time" class="block text-sm font-semibold text-gray-700 mb-2">Jam Tutup</label>
                    <div class="relative">
                        <input type="time" name="close_time" id="close_time" value="{{ old('close_time', isset($seller) && $seller->close_time ? date('H:i', strtotime($seller->close_time)) : '') }}" required
                            class="w-full bg-white border border-gray-200 focus:border-terracotta focus:ring-1 focus:ring-terracotta rounded-xl px-4 py-3 text-sm text-gray-800 placeholder-gray-400 font-medium transition-all">
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-6">
                <button type="submit" class="w-full bg-terracotta hover:bg-[#a6402d] text-white font-bold py-3.5 px-6 rounded-xl shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-terracotta uppercase tracking-wide text-sm">
                    SIMPAN PROFIL
                </button>
            </div>
        </form>

        <!-- Legal Verification Section (NEW) -->
        <div class="mt-8 bg-white p-8 rounded-[32px] shadow-sm border border-gray-100">
            <div class="flex items-center mb-6">
                <svg class="h-6 w-6 text-terracotta mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-xl font-bold text-gray-900">Legal Verification</h3>
            </div>

            <div class="flex flex-col md:flex-row gap-10 items-start">
                <div class="w-full md:w-1/2">
                    <p class="text-sm text-gray-500 mb-6 leading-relaxed">Untuk mulai berjualan, silakan unggah dokumen identitas usaha Anda (KTP, NIB, atau SIUP). Pastikan foto jelas dan terbaca. Format: PDF, JPG, PNG (Max 5MB).</p>
                    
                    <form action="{{ route('seller.upload-documents') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="bg-gray-50 border-4 border-dashed border-gray-100 rounded-[30px] p-10 text-center hover:bg-red-50/30 transition-all duration-300 group relative">
                            <input type="file" name="document" id="document" class="hidden" onchange="this.form.submit()">
                            <label for="document" class="cursor-pointer">
                                <div class="w-16 h-16 bg-white rounded-2xl shadow-sm flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                                    <svg class="w-8 h-8 text-terracotta" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                </div>
                                <span class="text-sm font-black text-gray-400 group-hover:text-terracotta transition-colors">Klik untuk Unggah Dokumen</span>
                            </label>
                        </div>
                    </form>
                </div>

                <div class="w-full md:w-1/2 bg-gray-50 rounded-[35px] p-10">
                    <h4 class="text-[10px] font-black text-gray-300 uppercase tracking-[0.2em] mb-6">Verification Status</h4>
                    @php
                        $status = $seller->verification_status ?? 'pending';
                        if (!is_string($status) || empty($status)) {
                            $status = 'pending';
                        }
                        $statusClasses = [
                            'pending' => 'bg-orange-100 text-orange-600',
                            'approved' => 'bg-green-100 text-green-600',
                            'rejected' => 'bg-red-100 text-red-600',
                            'suspended' => 'bg-gray-100 text-gray-600'
                        ];
                        $currentClass = $statusClasses[$status] ?? $statusClasses['pending'];
                        $bgColor = str_replace('text-', 'bg-', $currentClass);
                    @endphp
                    <div class="inline-flex items-center px-6 py-2 rounded-full text-xs font-black uppercase tracking-widest {{ $currentClass }}">
                        <span class="w-2 h-2 rounded-full mr-3 {{ $bgColor }} animate-pulse"></span>
                        {{ $status }}
                    </div>

                    @if($status === 'rejected' && $seller->rejection_reason)
                        <div class="mt-8 p-6 bg-red-50 border border-red-100 rounded-[25px]">
                            <p class="text-[10px] font-black text-red-400 uppercase tracking-widest mb-2">Alasan Penolakan</p>
                            <p class="text-sm text-red-700 font-bold leading-relaxed">{{ $seller->rejection_reason }}</p>
                        </div>
                        <p class="mt-4 text-[11px] text-gray-400 font-medium italic">Silakan unggah ulang dokumen yang valid untuk ditinjau kembali.</p>
                    @elseif($status === 'approved')
                        <div class="mt-8 p-6 bg-green-50 border border-green-100 rounded-[25px]">
                            <p class="text-sm text-green-700 font-bold leading-relaxed">Selamat! Akun Anda telah terverifikasi. Anda sekarang dapat mengelola katalog produk Sisa Rasa.</p>
                            <p class="mt-2 text-[10px] text-green-600 font-black uppercase tracking-widest">Verified at: {{ $seller->verified_at ? $seller->verified_at->format('d M Y, H:i') : '-' }}</p>
                        </div>
                    @else
                        <div class="mt-8 p-6 bg-white rounded-[25px] border border-gray-100">
                            <p class="text-sm text-gray-500 font-medium leading-relaxed italic">Dokumen Anda sedang dalam antrean peninjauan oleh tim kurator kami. Mohon tunggu maksimal 1x24 jam.</p>
                        </div>
                    @endif

                    @if($seller && $seller->document_path)
                        <div class="mt-10 pt-6 border-t border-gray-100">
                            <p class="text-[10px] font-black text-gray-300 uppercase tracking-widest mb-3">Dokumen Terunggah</p>
                            <div class="flex items-center bg-white p-4 rounded-2xl shadow-sm border border-gray-50">
                                <svg class="w-6 h-6 text-terracotta mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span class="text-xs font-bold text-gray-600 truncate">{{ basename($seller->document_path) }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Legal Verification Section (Retained for functionality, styled simply) --}}
    <div class="bg-gray-50 rounded-[24px] border border-gray-200 p-8">
        <div class="flex items-center mb-6">
            <svg class="h-6 w-6 text-gray-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="text-lg font-bold text-gray-800">Verifikasi Usaha</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <p class="text-sm text-gray-600 mb-4 leading-relaxed">Unggah dokumen identitas usaha (KTP, NIB, atau SIUP). Max 5MB (PDF/JPG/PNG).</p>
                <form action="{{ route('seller.upload-documents') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="relative">
                        <input type="file" name="document" id="document" class="hidden" onchange="this.form.submit()">
                        <label for="document" class="flex flex-col items-center justify-center w-full p-6 bg-white border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                            <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            <span class="text-xs font-semibold text-gray-500">Pilih File Dokumen</span>
                        </label>
                    </div>
                </form>
            </div>
            
            <div>
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Status Verifikasi</h4>
                @php
                    $status = $seller->verification_status ?? 'pending';
                    $statusColors = [
                        'pending' => 'bg-orange-100 text-orange-700 border-orange-200',
                        'approved' => 'bg-green-100 text-green-700 border-green-200',
                        'rejected' => 'bg-red-100 text-red-700 border-red-200',
                    ];
                    $color = $statusColors[$status] ?? $statusColors['pending'];
                @endphp
                <div class="inline-flex px-4 py-1.5 rounded-lg border {{ $color }} text-xs font-bold uppercase tracking-widest mb-4">
                    {{ $status }}
                </div>
                
                @if($status === 'rejected' && $seller->rejection_reason)
                    <div class="p-3 bg-red-50 text-red-700 text-xs font-medium rounded-lg border border-red-100">
                        <strong>Alasan Ditolak:</strong> {{ $seller->rejection_reason }}
                    </div>
                @elseif($status === 'approved')
                    <p class="text-sm text-green-600 font-medium">Akun terverifikasi. Anda dapat mengelola katalog produk.</p>
                @endif
                
                @if($seller && $seller->document_path)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-xs font-semibold text-gray-500 mb-1">Dokumen Terunggah:</p>
                        <p class="text-xs text-gray-700 truncate font-medium">{{ basename($seller->document_path) }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
