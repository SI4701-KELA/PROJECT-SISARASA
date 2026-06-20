@extends('layouts.seller')

@section('title', 'Komplain Masuk')

@php use Illuminate\Support\Facades\Storage; @endphp

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
  <div>
    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Toko Anda</p>
    <h1 class="text-3xl font-black text-gray-900 tracking-tight">Komplain Masuk</h1>
    <p class="text-sm text-gray-500 mt-1">Daftar keluhan dari pembeli terhadap <span class="font-bold text-gray-700">{{ $seller->store_name }}</span></p>
  </div>
  {{-- Stats Ringkasan --}}
  <div class="flex items-center gap-3">
    @php
      $menungguCount = $complaints->where('status_tiket', 'menunggu_seller')->count();
      $openCount     = $complaints->where('status_tiket', 'Open')->count();
      $prosesCount   = $complaints->where('status_tiket', 'Sedang Diproses')->count();
      $selesaiCount  = $complaints->where('status_tiket', 'Selesai')->count();
    @endphp
    @if($menungguCount)
    <div class="bg-orange-100 text-orange-700 px-4 py-2 rounded-2xl text-center">
      <p class="text-xl font-black">{{ $menungguCount }}</p>
      <p class="text-[10px] font-bold uppercase tracking-wide">Menunggu</p>
    </div>
    @endif
    @if($openCount)
    <div class="bg-amber-100 text-amber-700 px-4 py-2 rounded-2xl text-center">
      <p class="text-xl font-black">{{ $openCount }}</p>
      <p class="text-[10px] font-bold uppercase tracking-wide">Mediasi</p>
    </div>
    @endif
    @if($prosesCount)
    <div class="bg-blue-100 text-blue-700 px-4 py-2 rounded-2xl text-center">
      <p class="text-xl font-black">{{ $prosesCount }}</p>
      <p class="text-[10px] font-bold uppercase tracking-wide">Diproses</p>
    </div>
    @endif
    <div class="bg-gray-100 text-gray-600 px-4 py-2 rounded-2xl text-center">
      <p class="text-xl font-black">{{ $complaints->count() }}</p>
      <p class="text-[10px] font-bold uppercase tracking-wide">Total</p>
    </div>
  </div>
</div>

{{-- Flash Message --}}
@if(session('success'))
<div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-5 py-4 rounded-2xl text-sm font-medium flex items-center gap-2">
  <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
  {{ session('success') }}
</div>
@endif

{{-- Alert: Ada komplain yang perlu ditinjau --}}
@if($menungguCount > 0)
<div class="mb-6 bg-orange-50 border border-orange-200 rounded-2xl p-4 flex items-start gap-3">
  <div class="w-8 h-8 bg-orange-100 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5">
    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
  </div>
  <div>
    <p class="text-orange-800 font-bold text-sm">{{ $menungguCount }} komplain menunggu konfirmasi Anda</p>
    <p class="text-orange-700 text-xs mt-0.5">Sebagai seller, Anda perlu merespons apakah komplain ini valid atau perlu disanggah. Klik "Tinjau & Respons" untuk memberikan keputusan.</p>
  </div>
</div>
@endif

{{-- Alert: Ada sengketa yang sedang dimediasi Admin --}}
@if($openCount > 0)
<div class="mb-6 bg-blue-50 border border-blue-200 rounded-2xl p-4 flex items-start gap-3">
  <div class="w-8 h-8 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5">
    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
  </div>
  <div>
    <p class="text-blue-800 font-bold text-sm">{{ $openCount }} sengketa sedang dalam mediasi Admin</p>
    <p class="text-blue-700 text-xs mt-0.5">Admin platform sedang meninjau sanggahan Anda secara netral. Harap tunggu keputusan final dari Tim Sisa Rasa.</p>
  </div>
</div>
@endif

{{-- Complaint List --}}
@if($complaints->isEmpty())
<div class="bg-white rounded-3xl border border-gray-100 shadow-sm py-20 text-center">
  <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
  </div>
  <h3 class="text-lg font-black text-gray-800 mb-2">Tidak Ada Komplain 🎉</h3>
  <p class="text-sm text-gray-500">Tidak ada keluhan dari pembeli saat ini. Pertahankan kualitas toko Anda!</p>
</div>
@else
<div class="space-y-4">
  @foreach($complaints as $complaint)
  @php
    // Konfigurasi tampilan badge per status tiket
    $statusConfig = [
      'menunggu_seller' => ['bg'=>'bg-orange-100', 'text'=>'text-orange-700', 'dot'=>'bg-orange-400', 'border'=>'border-orange-200', 'label'=>'Menunggu Respons Anda'],
      'Open'            => ['bg'=>'bg-blue-100',   'text'=>'text-blue-700',   'dot'=>'bg-blue-500',   'border'=>'border-blue-200',   'label'=>'Mediasi Admin'],
      'Sedang Diproses' => ['bg'=>'bg-yellow-100', 'text'=>'text-yellow-700', 'dot'=>'bg-yellow-500', 'border'=>'border-yellow-200', 'label'=>'Sedang Diproses'],
      'Selesai'         => ['bg'=>'bg-green-100',  'text'=>'text-green-700',  'dot'=>'bg-green-500',  'border'=>'border-gray-100',   'label'=>'Selesai'],
    ];
    $st         = $statusConfig[$complaint->status_tiket] ?? $statusConfig['Open'];
    $needsAction = $complaint->status_tiket === 'menunggu_seller';
  @endphp
  <div class="bg-white rounded-3xl border {{ $st['border'] }} shadow-sm overflow-hidden {{ $needsAction ? 'ring-2 ring-orange-200' : '' }}">
    <div class="p-5">
      {{-- Top Row --}}
      <div class="flex items-start justify-between gap-3 mb-4">
        <div class="min-w-0">
          <div class="flex items-center gap-2 mb-1.5">
            <span class="text-xs font-bold text-gray-400">#{{ $complaint->id }}</span>
            @if($needsAction)
            <span class="bg-orange-500 text-white text-[9px] font-black px-2 py-0.5 rounded-full uppercase tracking-wide animate-pulse">Perlu Aksi</span>
            @endif
            <span class="text-gray-300">·</span>
            <span class="text-xs text-gray-400">{{ $complaint->created_at->diffForHumans() }}</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-7 h-7 bg-gray-100 rounded-full flex items-center justify-center text-gray-600 font-black text-xs flex-shrink-0">
              {{ strtoupper(substr($complaint->buyer->name ?? 'B', 0, 1)) }}
            </div>
            <div>
              <p class="font-bold text-gray-900 text-sm">{{ $complaint->buyer->name ?? 'Pembeli' }}</p>
              <p class="text-xs text-gray-400">{{ $complaint->buyer->email ?? '' }}</p>
            </div>
          </div>
        </div>
        {{-- Status Badge --}}
        <span class="flex-shrink-0 inline-flex items-center gap-1.5 {{ $st['bg'] }} {{ $st['text'] }} text-xs font-bold px-3 py-1.5 rounded-full">
          <span class="w-1.5 h-1.5 {{ $st['dot'] }} rounded-full {{ $needsAction ? 'animate-pulse' : '' }}"></span>
          {{ $st['label'] }}
        </span>
      </div>

      {{-- Kategori & Deskripsi --}}
      <div class="bg-gray-50 rounded-2xl p-4 mb-4">
        <div class="flex items-center gap-2 mb-2">
          <span class="bg-red-100 text-red-700 text-[11px] font-bold px-2.5 py-1 rounded-lg">{{ $complaint->kategori_masalah }}</span>
        </div>
        <p class="text-sm text-gray-600 leading-relaxed line-clamp-2">{{ $complaint->deskripsi }}</p>
      </div>

      {{-- Foto Bukti Pembeli --}}
      @if($complaint->foto_bukti)
      <div class="mb-4">
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Foto Bukti Pembeli</p>
        <img src="{{ Storage::url($complaint->foto_bukti) }}"
             alt="Foto bukti"
             class="w-full max-h-40 object-cover rounded-2xl border border-gray-100 shadow-sm">
      </div>
      @endif

      {{-- Tombol Aksi --}}
      <div class="flex gap-2">
        @if($needsAction)
        <a href="{{ route('seller.complaints.show', $complaint->id) }}"
           class="flex-1 inline-flex items-center justify-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm px-4 py-2.5 rounded-2xl transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
          Tinjau & Respons
        </a>
        @else
        <a href="{{ route('seller.complaints.show', $complaint->id) }}"
           class="flex-1 inline-flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold text-sm px-4 py-2.5 rounded-2xl transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
          Lihat Detail
        </a>
        @endif
      </div>
    </div>
  </div>
  @endforeach
</div>
@endif

@endsection
