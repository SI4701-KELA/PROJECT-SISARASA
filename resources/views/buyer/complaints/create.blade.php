@extends('layouts.buyer')

@section('title', 'Ajukan Komplain - ' . $seller->store_name)

@section('content')

{{-- Header --}}
<div class="mb-6">
  <a href="{{ route('buyer.stores') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 font-medium mb-4 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    Kembali
  </a>
  <div style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);" class="rounded-3xl p-6 text-white shadow-xl shadow-red-200 mb-6">
    <div class="flex items-center gap-3 mb-3">
      <div class="w-10 h-10 bg-white/20 rounded-2xl flex items-center justify-center">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
      </div>
      <div>
        <p class="text-red-200 text-xs font-semibold uppercase tracking-wider">Pusat Bantuan Komplain</p>
        <h1 class="text-xl font-black leading-tight">Ajukan Keluhan</h1>
      </div>
    </div>
    <p class="text-red-100 text-sm leading-relaxed">Anda mengajukan komplain untuk toko <span class="font-bold text-white">{{ $seller->store_name }}</span>. Tim kami akan meninjau dalam 1×24 jam.</p>
  </div>
</div>

{{-- Existing Complaint Warning --}}
@if($existingComplaint)
<div class="mb-6 bg-amber-50 border border-amber-200 rounded-2xl p-5">
  <div class="flex items-start gap-3">
    <div class="w-8 h-8 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5">
      <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    </div>
    <div>
      <p class="text-amber-800 font-bold text-sm">Ada Tiket Komplain Aktif</p>
      <p class="text-amber-700 text-xs mt-1">Anda sudah memiliki komplain aktif (#{{ $existingComplaint->id }}) ke toko ini dengan status <strong>{{ $existingComplaint->status_tiket }}</strong>. Tunggu hingga tiket tersebut selesai sebelum mengajukan yang baru.</p>
      <a href="{{ route('buyer.complaints.index') }}" class="inline-block mt-2 text-xs font-bold text-amber-700 underline">Lihat Riwayat Komplain →</a>
    </div>
  </div>
</div>
@endif

{{-- Validation Errors --}}
@if($errors->any())
<div class="mb-6 bg-red-50 border border-red-200 rounded-2xl p-4">
  <p class="text-red-700 font-bold text-sm mb-2">⚠️ Ada kesalahan pada formulir:</p>
  <ul class="list-disc list-inside space-y-1">
    @foreach($errors->all() as $error)
      <li class="text-red-600 text-xs">{{ $error }}</li>
    @endforeach
  </ul>
</div>
@endif

{{-- Form --}}
<div x-data="complaintForm()" class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden max-w-2xl">
  <form method="POST"
        action="{{ route('buyer.complaint.store', $seller) }}"
        enctype="multipart/form-data"
        class="p-7 space-y-6"
        @if($existingComplaint) onsubmit="return false;" @endif>
    @csrf

    {{-- Kategori Masalah --}}
    <div>
      <label for="kategori_masalah" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">
        Kategori Masalah <span class="text-red-500">*</span>
      </label>
      <select id="kategori_masalah" name="kategori_masalah" x-model="kategori" required
        class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-red-200 focus:border-red-300 transition-all appearance-none cursor-pointer">
        <option value="">-- Pilih Kategori Masalah --</option>
        @foreach(['Pesanan Tidak Sesuai', 'Porsi Kurang', 'Kualitas Buruk/Basi', 'Lainnya'] as $kat)
          <option value="{{ $kat }}" {{ old('kategori_masalah') === $kat ? 'selected' : '' }}>{{ $kat }}</option>
        @endforeach
      </select>
      @error('kategori_masalah')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
      @enderror
    </div>

    {{-- Deskripsi --}}
    <div>
      <label for="deskripsi" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">
        Deskripsi Lengkap Keluhan <span class="text-red-500">*</span>
      </label>
      <textarea id="deskripsi" name="deskripsi" rows="5" required minlength="20"
        placeholder="Ceritakan detail masalah yang Anda alami. Semakin detail, semakin cepat kami bisa membantu..."
        class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3 text-sm text-gray-700 placeholder-gray-300 resize-none focus:outline-none focus:ring-2 focus:ring-red-200 focus:border-red-300 transition-all">{{ old('deskripsi') }}</textarea>
      <p class="text-gray-400 text-xs mt-1">Minimal 20 karakter</p>
      @error('deskripsi')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
      @enderror
    </div>

    {{-- Foto Bukti (Dynamic Requirement) --}}
    <div>
      <label for="foto_bukti" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">
        Foto Bukti
        <span x-show="kategori === 'Kualitas Buruk/Basi'" class="text-red-500 font-black" x-transition>
          * (Wajib untuk kategori ini)
        </span>
        <span x-show="kategori !== 'Kualitas Buruk/Basi'" class="text-gray-400 font-normal">(Opsional)</span>
      </label>

      <input type="file" id="foto_bukti" name="foto_bukti"
        accept="image/jpeg,image/png,image/webp"
        @change="previewImage($event)"
        :required="kategori === 'Kualitas Buruk/Basi'"
        class="w-full bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl px-4 py-6 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-red-50 file:text-red-600 file:font-semibold file:text-xs cursor-pointer hover:border-red-200 transition-colors"
        :class="{ 'border-red-300 bg-red-50': kategori === 'Kualitas Buruk/Basi' && !hasImage, 'border-green-300 bg-green-50': hasImage }">

      {{-- Preview --}}
      <div x-show="previewUrl" x-transition class="mt-3">
        <img :src="previewUrl" alt="Preview" class="w-full max-h-48 object-cover rounded-2xl border border-gray-200 shadow-sm">
        <p class="text-green-600 text-xs font-medium mt-1">✓ Foto siap diunggah</p>
      </div>

      {{-- Alert Wajib --}}
      <div x-show="kategori === 'Kualitas Buruk/Basi' && !hasImage" x-transition
        class="mt-2 flex items-center gap-2 text-red-500 text-xs font-medium">
        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
        Foto bukti wajib untuk kategori Kualitas Buruk/Basi
      </div>

      <p class="text-gray-400 text-xs mt-1">Format: JPG, PNG, WebP. Maks: 2MB</p>
      @error('foto_bukti')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
      @enderror
    </div>

    {{-- Info Toko --}}
    <div class="bg-gray-50 rounded-2xl p-4 flex items-center gap-3">
      <div class="w-10 h-10 bg-teal-100 rounded-xl flex items-center justify-center flex-shrink-0">
        <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
      </div>
      <div class="min-w-0">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Toko yang Dikomplain</p>
        <p class="text-sm font-bold text-gray-800 truncate">{{ $seller->store_name }}</p>
        <p class="text-xs text-gray-500 truncate">{{ $seller->address }}</p>
      </div>
    </div>

    {{-- Submit --}}
    <button type="submit"
      :disabled="{{ $existingComplaint ? 'true' : 'false' }}"
      style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);"
      class="w-full text-white font-bold py-4 rounded-2xl shadow-lg shadow-red-200 hover:opacity-90 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
      @if($existingComplaint)
        Sudah Ada Tiket Aktif
      @else
        Kirim Komplain
      @endif
    </button>

    <p class="text-center text-xs text-gray-400">Dengan mengirim formulir ini, Anda menyetujui Kebijakan Penggunaan Platform Sisa Rasa.</p>
  </form>
</div>

@endsection

@push('scripts')
<script>
function complaintForm() {
  return {
    kategori: '{{ old('kategori_masalah', '') }}',
    previewUrl: null,
    hasImage: false,
    previewImage(event) {
      const file = event.target.files[0];
      if (file) {
        this.hasImage = true;
        this.previewUrl = URL.createObjectURL(file);
      } else {
        this.hasImage = false;
        this.previewUrl = null;
      }
    }
  }
}
</script>
@endpush
