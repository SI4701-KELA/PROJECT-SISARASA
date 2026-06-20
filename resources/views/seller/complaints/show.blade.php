@extends('layouts.seller')

@section('title', 'Detail Komplain #' . $complaint->id)

@php use Illuminate\Support\Facades\Storage; @endphp

@section('content')

{{-- Breadcrumb --}}
<div class="flex items-center gap-2 mb-6 text-sm text-gray-400">
  <a href="{{ route('seller.complaints.index') }}" class="hover:text-gray-700 font-medium transition-colors">Komplain Masuk</a>
  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
  <span class="text-gray-600 font-bold">Tiket #{{ $complaint->id }}</span>
</div>

{{-- Validation Errors --}}
@if($errors->any())
<div class="mb-6 bg-red-50 border border-red-200 rounded-2xl p-4">
  <p class="text-red-700 font-bold text-sm mb-2">⚠️ Terdapat kesalahan pada formulir Anda:</p>
  <ul class="list-disc list-inside space-y-1">
    @foreach($errors->all() as $error)
    <li class="text-red-600 text-sm">{{ $error }}</li>
    @endforeach
  </ul>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

  {{-- ==============================
       KOLOM KIRI: Detail Komplain Pembeli
       ============================== --}}
  <div class="lg:col-span-2 space-y-5">

    {{-- Header Tiket --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
      <div class="flex items-start justify-between gap-4 mb-5">
        <div>
          <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Tiket Komplain</p>
          <h1 class="text-2xl font-black text-gray-900">#{{ $complaint->id }}</h1>
          <p class="text-sm text-gray-400 mt-1">Diajukan {{ $complaint->created_at->diffForHumans() }} · {{ $complaint->created_at->format('d M Y, H:i') }}</p>
        </div>
        {{-- Status Badge --}}
        @php
          $statusConfig = [
            'menunggu_seller' => ['bg'=>'bg-orange-100', 'text'=>'text-orange-700', 'dot'=>'bg-orange-400', 'label'=>'Menunggu Respons Anda'],
            'Open'            => ['bg'=>'bg-blue-100',   'text'=>'text-blue-700',   'dot'=>'bg-blue-500',   'label'=>'Mediasi Admin'],
            'Sedang Diproses' => ['bg'=>'bg-yellow-100', 'text'=>'text-yellow-700', 'dot'=>'bg-yellow-500', 'label'=>'Sedang Diproses'],
            'Selesai'         => ['bg'=>'bg-green-100',  'text'=>'text-green-700',  'dot'=>'bg-green-500',  'label'=>'Selesai'],
          ];
          $st = $statusConfig[$complaint->status_tiket] ?? $statusConfig['Open'];
        @endphp
        <span class="flex-shrink-0 inline-flex items-center gap-1.5 {{ $st['bg'] }} {{ $st['text'] }} text-sm font-bold px-4 py-2 rounded-full">
          <span class="w-2 h-2 {{ $st['dot'] }} rounded-full {{ $complaint->status_tiket === 'menunggu_seller' ? 'animate-pulse' : '' }}"></span>
          {{ $st['label'] }}
        </span>
      </div>

      {{-- Info Pembeli --}}
      <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-2xl">
        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center text-red-600 font-black text-base flex-shrink-0">
          {{ strtoupper(substr($complaint->buyer->name ?? 'B', 0, 1)) }}
        </div>
        <div>
          <p class="font-bold text-gray-900">{{ $complaint->buyer->name ?? 'Pembeli' }}</p>
          <p class="text-xs text-gray-400">{{ $complaint->buyer->email ?? '-' }}</p>
        </div>
      </div>
    </div>

    {{-- Detail Keluhan --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
      <h2 class="text-sm font-black text-gray-700 uppercase tracking-widest mb-4">Detail Keluhan Pembeli</h2>

      {{-- Kategori Masalah --}}
      <div class="mb-5">
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Kategori Masalah</p>
        <span class="inline-block bg-red-100 text-red-700 font-bold text-sm px-3 py-1.5 rounded-xl">
          {{ $complaint->kategori_masalah }}
        </span>
      </div>

      {{-- Kronologi / Deskripsi --}}
      <div class="mb-5">
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Kronologi Keluhan</p>
        <div class="bg-gray-50 rounded-2xl p-4">
          <p class="text-sm text-gray-700 leading-relaxed">{{ $complaint->deskripsi }}</p>
        </div>
      </div>

      {{-- Foto Bukti Pembeli --}}
      @if($complaint->foto_bukti)
      <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Foto Bukti dari Pembeli</p>
        <img src="{{ Storage::url($complaint->foto_bukti) }}"
             alt="Foto bukti komplain"
             class="w-full max-h-72 object-cover rounded-2xl border border-gray-200 shadow-sm cursor-pointer hover:opacity-90 transition-opacity"
             onclick="this.requestFullscreen?.() || this.webkitRequestFullscreen?.()">
        <p class="text-xs text-gray-400 mt-1.5 text-center">Klik gambar untuk memperbesar</p>
      </div>
      @else
      <div class="bg-gray-50 border border-dashed border-gray-200 rounded-2xl p-4 text-center">
        <p class="text-xs text-gray-400">Pembeli tidak melampirkan foto bukti.</p>
      </div>
      @endif
    </div>

    {{-- Sanggahan Seller (tampil jika sudah merespons) --}}
    @if($complaint->seller_action)
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
      <h2 class="text-sm font-black text-gray-700 uppercase tracking-widest mb-4">Respons Anda</h2>
      <div class="flex items-center gap-2 mb-3">
        @if($complaint->seller_action === 'approved')
        <span class="inline-flex items-center gap-1.5 bg-green-100 text-green-700 text-xs font-bold px-3 py-1.5 rounded-full">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
          Anda menyetujui komplain ini
        </span>
        @else
        <span class="inline-flex items-center gap-1.5 bg-red-100 text-red-700 text-xs font-bold px-3 py-1.5 rounded-full">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
          Anda menolak/menyangkal komplain ini
        </span>
        @endif
        @if($complaint->seller_responded_at)
        <span class="text-xs text-gray-400">· {{ $complaint->seller_responded_at->diffForHumans() }}</span>
        @endif
      </div>
      <div class="bg-gray-50 rounded-2xl p-4 mb-3">
        <p class="text-sm text-gray-700 leading-relaxed">{{ $complaint->seller_reason }}</p>
      </div>
      @if($complaint->seller_proof_path)
      <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Foto Bukti yang Anda Unggah</p>
        <img src="{{ Storage::url($complaint->seller_proof_path) }}"
             alt="Foto bukti seller"
             class="w-full max-h-48 object-cover rounded-2xl border border-gray-100">
      </div>
      @endif
    </div>
    @endif

  </div>

  {{-- ==============================
       KOLOM KANAN: Panel Aksi
       ============================== --}}
  <div class="space-y-5">

    @if($complaint->status_tiket === 'menunggu_seller')
    {{-- ===== FORM AKSI SELLER (status: menunggu_seller) ===== --}}
    <div class="bg-white rounded-3xl border border-orange-200 shadow-sm p-6">
      <div class="flex items-center gap-2 mb-5">
        <div class="w-8 h-8 bg-orange-100 rounded-xl flex items-center justify-center flex-shrink-0">
          <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
          <h2 class="font-black text-gray-900 text-sm">Berikan Keputusan Anda</h2>
          <p class="text-xs text-gray-400">Respons ini bersifat final dan tidak dapat diubah</p>
        </div>
      </div>

      <form action="{{ route('seller.complaints.respond', $complaint->id) }}" method="POST" enctype="multipart/form-data" id="complaint-respond-form">
        @csrf

        {{-- Radio: Setuju atau Tolak --}}
        <div class="space-y-3 mb-5" id="action-radio-group">
          {{-- Opsi Setuju --}}
          <label for="action-approved" class="flex items-start gap-3 p-4 border-2 border-gray-200 rounded-2xl cursor-pointer hover:border-green-300 hover:bg-green-50 transition-all has-[:checked]:border-green-400 has-[:checked]:bg-green-50">
            <input type="radio" id="action-approved" name="seller_action" value="approved"
                   class="mt-0.5 text-green-600 focus:ring-green-500"
                   onchange="handleActionChange(this.value)">
            <div>
              <p class="font-bold text-gray-800 text-sm">✅ Setujui Komplain</p>
              <p class="text-xs text-gray-500 mt-0.5">Saya mengakui adanya masalah dan menyetujui klaim pembeli. Tiket akan langsung ditandai Selesai.</p>
            </div>
          </label>

          {{-- Opsi Tolak --}}
          <label for="action-rejected" class="flex items-start gap-3 p-4 border-2 border-gray-200 rounded-2xl cursor-pointer hover:border-red-300 hover:bg-red-50 transition-all has-[:checked]:border-red-400 has-[:checked]:bg-red-50">
            <input type="radio" id="action-rejected" name="seller_action" value="rejected"
                   class="mt-0.5 text-red-600 focus:ring-red-500"
                   onchange="handleActionChange(this.value)">
            <div>
              <p class="font-bold text-gray-800 text-sm">❌ Tolak & Sanggah</p>
              <p class="text-xs text-gray-500 mt-0.5">Saya tidak setuju dengan komplain ini dan ingin memberikan sanggahan beserta bukti. Admin akan memediasi.</p>
            </div>
          </label>
        </div>

        {{-- Panel Sanggahan — tersembunyi secara default, muncul jika "Tolak" dipilih --}}
        <div id="rejection-panel" class="hidden space-y-4 border-t border-dashed border-gray-200 pt-5">
          <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Detail Sanggahan</p>

          {{-- Alasan Sanggahan --}}
          <div>
            <label for="seller_reason" class="block text-xs font-bold text-gray-700 mb-1.5">
              Alasan Sanggahan <span class="text-red-500">*</span>
            </label>
            <textarea id="seller_reason" name="seller_reason" rows="4"
                      placeholder="Jelaskan secara detail mengapa Anda menolak komplain ini. Misalnya: produk dalam kondisi baik saat dikemas, ada foto dokumentasi, dll. (minimal 10 karakter)"
                      class="w-full border border-gray-200 rounded-2xl px-4 py-3 text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-200 focus:border-red-400 resize-none transition-colors @error('seller_reason') border-red-400 @enderror">{{ old('seller_reason') }}</textarea>
            @error('seller_reason')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          {{-- Upload Foto Bukti Kelayakan Produk --}}
          <div>
            <label for="seller_proof" class="block text-xs font-bold text-gray-700 mb-1.5">
              Foto Bukti Kelayakan Produk <span class="text-red-500">*</span>
            </label>
            <div class="relative border-2 border-dashed border-gray-200 rounded-2xl p-4 text-center hover:border-red-300 transition-colors cursor-pointer"
                 onclick="document.getElementById('seller_proof').click()">
              <input type="file" id="seller_proof" name="seller_proof" accept="image/*"
                     class="hidden"
                     onchange="previewProof(this)">
              <div id="proof-placeholder">
                <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <p class="text-xs font-bold text-gray-500">Klik untuk unggah foto bukti</p>
                <p class="text-[10px] text-gray-400 mt-0.5">JPG, PNG, WebP · Maks. 2MB</p>
              </div>
              <div id="proof-preview" class="hidden">
                <img id="proof-preview-img" src="" alt="Preview" class="w-full max-h-32 object-cover rounded-xl mx-auto mb-1">
                <p class="text-xs text-gray-500" id="proof-filename"></p>
              </div>
            </div>
            @error('seller_proof')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>
        </div>

        {{-- Tombol Submit --}}
        <div class="mt-5">
          <button type="submit" id="submit-btn"
                  class="w-full py-3 px-4 bg-gray-200 text-gray-400 font-bold rounded-2xl text-sm cursor-not-allowed transition-all"
                  disabled>
            Pilih salah satu opsi di atas
          </button>
        </div>
      </form>
    </div>

    @elseif($complaint->status_tiket === 'Open')
    {{-- ===== PANEL MEDIASI ADMIN (status: Open setelah seller menolak) ===== --}}
    <div class="bg-white rounded-3xl border border-blue-200 shadow-sm p-6">
      <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 mb-5">
        <div class="flex items-start gap-3">
          <div class="w-8 h-8 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
          </div>
          <div>
            <p class="font-black text-blue-800 text-sm">Dalam Proses Mediasi Admin</p>
            <p class="text-blue-700 text-xs mt-1 leading-relaxed">
              Anda telah menyampaikan sanggahan. Admin platform (Tim Sisa Rasa) sedang meninjau sengketa ini secara netral berdasarkan bukti dari kedua pihak. Harap tunggu keputusan final.
            </p>
          </div>
        </div>
      </div>

      <p class="text-xs text-gray-500 mb-4">Jika Anda memiliki pertanyaan atau ingin memberikan informasi tambahan kepada Admin mediasi, gunakan fitur Live Chat:</p>

      {{-- Tombol Hubungi Admin Mediasi --}}
      @php
        // Cari user Admin pertama untuk Live Chat
        $adminUser = \App\Models\User::where('role', 'admin')->first();
      @endphp
      @if($adminUser)
      <a href="{{ route('chat.show', ['contact' => $adminUser->id]) }}?complaint_id={{ $complaint->id }}"
         class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm px-4 py-3 rounded-2xl transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        Hubungi Admin Mediasi
      </a>
      @else
      <div class="bg-gray-50 rounded-2xl p-3 text-center">
        <p class="text-xs text-gray-400">Admin mediasi sedang tidak tersedia.</p>
      </div>
      @endif
    </div>

    @elseif($complaint->status_tiket === 'Selesai')
    {{-- ===== PANEL SELESAI ===== --}}
    <div class="bg-white rounded-3xl border border-green-200 shadow-sm p-6 text-center">
      <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
      </div>
      <p class="font-black text-green-800">Tiket Telah Selesai</p>
      <p class="text-xs text-green-700 mt-1">Komplain ini sudah ditangani dan ditutup.</p>

      {{-- Tampilkan balasan Admin jika ada --}}
      @if($complaint->balasan_admin)
      <div class="mt-4 bg-green-50 rounded-2xl p-4 text-left">
        <p class="text-[10px] font-bold text-green-600 uppercase tracking-widest mb-1.5">Keputusan Admin</p>
        <p class="text-sm text-green-800 leading-relaxed">{{ $complaint->balasan_admin }}</p>
      </div>
      @endif
    </div>

    @else
    {{-- Status lain (Sedang Diproses) --}}
    <div class="bg-white rounded-3xl border border-yellow-200 shadow-sm p-6 text-center">
      <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-3">
        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <p class="font-black text-yellow-800">Sedang Diproses Admin</p>
      <p class="text-xs text-yellow-700 mt-1">Tim Sisa Rasa sedang menangani komplain ini.</p>
    </div>
    @endif

    {{-- Tombol Kembali --}}
    <a href="{{ route('seller.complaints.index') }}"
       class="w-full inline-flex items-center justify-center gap-2 bg-white border border-gray-200 text-gray-600 font-semibold text-sm px-4 py-2.5 rounded-2xl hover:bg-gray-50 transition-colors shadow-sm">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
      Kembali ke Daftar
    </a>

  </div>
</div>

@endsection

@push('scripts')
<script>
/**
 * Menangani tampilan dinamis form sanggahan berdasarkan pilihan radio button.
 * Panel sanggahan hanya muncul jika Seller memilih opsi "Tolak".
 * Tombol submit diaktifkan ketika salah satu opsi sudah dipilih.
 *
 * Menggunakan Vanilla JavaScript tanpa dependensi eksternal.
 */
function handleActionChange(value) {
  const rejectionPanel = document.getElementById('rejection-panel');
  const submitBtn = document.getElementById('submit-btn');
  const sellerReasonField = document.getElementById('seller_reason');
  const sellerProofField = document.getElementById('seller_proof');

  if (value === 'rejected') {
    // Tampilkan panel sanggahan dengan animasi fade-in
    rejectionPanel.classList.remove('hidden');
    rejectionPanel.style.opacity = '0';
    requestAnimationFrame(() => {
      rejectionPanel.style.transition = 'opacity 0.25s ease';
      rejectionPanel.style.opacity = '1';
    });

    // Aktifkan validasi required secara programatik
    sellerReasonField.setAttribute('required', 'required');
    sellerProofField.setAttribute('required', 'required');

    // Update tombol submit menjadi merah untuk konfirmasi penolakan
    submitBtn.disabled = false;
    submitBtn.textContent = 'Kirim Sanggahan ke Admin';
    submitBtn.className = 'w-full py-3 px-4 bg-red-600 hover:bg-red-700 text-white font-bold rounded-2xl text-sm cursor-pointer transition-all';

  } else if (value === 'approved') {
    // Sembunyikan panel sanggahan dan hapus required
    rejectionPanel.classList.add('hidden');
    sellerReasonField.removeAttribute('required');
    sellerProofField.removeAttribute('required');

    // Update tombol submit menjadi hijau untuk konfirmasi persetujuan
    submitBtn.disabled = false;
    submitBtn.textContent = '✅ Konfirmasi: Setujui Komplain Pembeli';
    submitBtn.className = 'w-full py-3 px-4 bg-green-600 hover:bg-green-700 text-white font-bold rounded-2xl text-sm cursor-pointer transition-all';
  }
}

/**
 * Preview foto bukti yang diunggah seller sebelum form di-submit.
 */
function previewProof(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('proof-placeholder').classList.add('hidden');
      document.getElementById('proof-preview').classList.remove('hidden');
      document.getElementById('proof-preview-img').src = e.target.result;
      document.getElementById('proof-filename').textContent = input.files[0].name;
    };
    reader.readAsDataURL(input.files[0]);
  }
}

// Konfirmasi sebelum submit untuk mencegah aksi tidak disengaja
document.getElementById('complaint-respond-form')?.addEventListener('submit', function(e) {
  const action = document.querySelector('input[name="seller_action"]:checked')?.value;
  if (!action) {
    e.preventDefault();
    alert('Pilih terlebih dahulu apakah Anda menyetujui atau menolak komplain ini.');
    return;
  }

  const message = action === 'approved'
    ? 'Anda akan MENYETUJUI klaim pembeli. Tindakan ini tidak dapat dibatalkan. Lanjutkan?'
    : 'Anda akan MENOLAK komplain ini dan meminta mediasi Admin. Tindakan ini tidak dapat dibatalkan. Lanjutkan?';

  if (!confirm(message)) {
    e.preventDefault();
  }
});
</script>
@endpush
