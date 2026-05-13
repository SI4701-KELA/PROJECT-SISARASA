@extends('layouts.buyer')

@section('title', 'Pusat Bantuan')

@php use Illuminate\Support\Facades\Storage; @endphp

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
  <div>
    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Pusat Bantuan</p>
    <h1 class="text-3xl font-black text-gray-900 tracking-tight">Riwayat Komplain</h1>
    <p class="text-sm text-gray-500 mt-1">Pantau status tiket keluhan yang telah Anda ajukan.</p>
  </div>
  <a href="{{ route('buyer.stores') }}"
    class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-600 font-semibold text-sm px-4 py-2.5 rounded-2xl hover:bg-gray-50 transition-colors shadow-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
    Jelajahi Toko
  </a>
</div>

{{-- Flash Message --}}
@if(session('success'))
<div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-5 py-4 rounded-2xl text-sm font-medium flex items-center gap-2">
  <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
  {{ session('success') }}
</div>
@endif

{{-- Stats --}}
@if($complaints->count() > 0)
@php
  $open    = $complaints->where('status_tiket', 'Open')->count();
  $proses  = $complaints->where('status_tiket', 'Sedang Diproses')->count();
  $selesai = $complaints->where('status_tiket', 'Selesai')->count();
@endphp
<div class="grid grid-cols-3 gap-4 mb-6">
  <div class="bg-white rounded-2xl p-4 text-center shadow-sm border border-gray-100">
    <p class="text-2xl font-black text-amber-500">{{ $open }}</p>
    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mt-1">Open</p>
  </div>
  <div class="bg-white rounded-2xl p-4 text-center shadow-sm border border-gray-100">
    <p class="text-2xl font-black text-blue-500">{{ $proses }}</p>
    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mt-1">Diproses</p>
  </div>
  <div class="bg-white rounded-2xl p-4 text-center shadow-sm border border-gray-100">
    <p class="text-2xl font-black text-green-500">{{ $selesai }}</p>
    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mt-1">Selesai</p>
  </div>
</div>
@endif

{{-- Complaint Cards --}}
@forelse($complaints as $complaint)
@php
  $statusConfig = [
    'Open'           => ['bg' => 'bg-amber-100',  'text' => 'text-amber-700',  'dot' => 'bg-amber-400',  'label' => 'Open'],
    'Sedang Diproses'=> ['bg' => 'bg-blue-100',   'text' => 'text-blue-700',   'dot' => 'bg-blue-500',   'label' => 'Sedang Diproses'],
    'Selesai'        => ['bg' => 'bg-green-100',  'text' => 'text-green-700',  'dot' => 'bg-green-500',  'label' => 'Selesai'],
  ];
  $st = $statusConfig[$complaint->status_tiket] ?? $statusConfig['Open'];
@endphp
<div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-4 hover:shadow-md transition-shadow">

  <div class="p-5 pb-4">
    <div class="flex items-start justify-between gap-3 mb-3">
      <div class="min-w-0">
        <div class="flex items-center gap-2 mb-1">
          <span class="text-xs font-bold text-gray-400">#{{ $complaint->id }}</span>
          <span class="text-gray-300">·</span>
          <span class="text-xs text-gray-400">{{ $complaint->created_at->diffForHumans() }}</span>
        </div>
        <h3 class="font-black text-gray-900 text-base leading-tight truncate">{{ $complaint->seller->store_name ?? 'Toko Tidak Ditemukan' }}</h3>
        <p class="text-xs text-gray-500 mt-0.5">{{ $complaint->seller->address ?? '-' }}</p>
      </div>
      {{-- Status Badge --}}
      <span class="flex-shrink-0 inline-flex items-center gap-1.5 {{ $st['bg'] }} {{ $st['text'] }} text-xs font-bold px-3 py-1.5 rounded-full">
        <span class="w-1.5 h-1.5 {{ $st['dot'] }} rounded-full {{ $complaint->status_tiket === 'Open' ? 'animate-pulse' : '' }}"></span>
        {{ $st['label'] }}
      </span>
    </div>

    {{-- Kategori & Deskripsi --}}
    <div class="bg-gray-50 rounded-2xl p-4 mb-3">
      <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Kategori Masalah</p>
      <p class="text-sm font-bold text-gray-800 mb-3">{{ $complaint->kategori_masalah }}</p>
      <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Deskripsi</p>
      <p class="text-sm text-gray-600 leading-relaxed">{{ $complaint->deskripsi }}</p>
    </div>

    {{-- Foto Bukti --}}
    @if($complaint->foto_bukti)
    <div class="mb-3">
      <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Foto Bukti</p>
      <img src="{{ Storage::url($complaint->foto_bukti) }}"
           alt="Foto bukti komplain"
           class="w-full max-h-52 object-cover rounded-2xl border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity"
           onclick="this.requestFullscreen?.() || this.webkitRequestFullscreen?.()">
    </div>
    @endif
  </div>

  {{-- Admin Reply Block (TC-CMP-006) --}}
  @if($complaint->balasan_admin)
  <div class="mx-5 mb-5 bg-green-50 border border-green-200 rounded-2xl p-4">
    <div class="flex items-center gap-2 mb-2">
      <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
      </div>
      <p class="text-xs font-black text-green-700 uppercase tracking-wide">Balasan Tim Sisa Rasa</p>
    </div>
    <p class="text-sm text-green-800 leading-relaxed">{{ $complaint->balasan_admin }}</p>
  </div>
  @else
  <div class="mx-5 mb-5 bg-gray-50 border border-dashed border-gray-200 rounded-2xl p-4 text-center">
    <p class="text-xs text-gray-400 font-medium">⏳ Menunggu balasan dari tim kami...</p>
  </div>
  @endif

</div>
@empty
{{-- Empty State --}}
<div class="text-center py-20">
  <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-5">
    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
    </svg>
  </div>
  <h3 class="text-xl font-black text-gray-800 mb-2">Belum Ada Komplain</h3>
  <p class="text-sm text-gray-500 mb-6 max-w-xs mx-auto">Anda belum pernah mengajukan komplain. Jika ada masalah dengan pembelian, jangan ragu untuk melapor.</p>
  <a href="{{ route('buyer.stores') }}"
    class="inline-flex items-center gap-2 font-bold px-6 py-3 rounded-2xl transition-colors text-white"
    style="background-color: #2aab7f;">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
    Jelajahi Toko
  </a>
</div>
@endforelse

@endsection
