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
  {{-- Stats --}}
  <div class="flex items-center gap-3">
    @php
      $openCount    = $complaints->where('status_tiket', 'Open')->count();
      $prosesCount  = $complaints->where('status_tiket', 'Sedang Diproses')->count();
      $selesaiCount = $complaints->where('status_tiket', 'Selesai')->count();
    @endphp
    @if($openCount)
    <div class="bg-amber-100 text-amber-700 px-4 py-2 rounded-2xl text-center">
      <p class="text-xl font-black">{{ $openCount }}</p>
      <p class="text-[10px] font-bold uppercase tracking-wide">Open</p>
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

{{-- Alert Penting --}}
@if($openCount > 0)
<div class="mb-6 bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-start gap-3">
  <div class="w-8 h-8 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5">
    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
  </div>
  <div>
    <p class="text-amber-800 font-bold text-sm">Ada {{ $openCount }} komplain baru yang belum ditangani</p>
    <p class="text-amber-700 text-xs mt-0.5">Komplain ini sedang ditinjau oleh Tim Sisa Rasa. Pastikan Anda telah memahami dan siap memberikan keterangan jika dibutuhkan.</p>
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
    $statusConfig = [
      'Open'           => ['bg'=>'bg-amber-100', 'text'=>'text-amber-700', 'dot'=>'bg-amber-400', 'border'=>'border-amber-200'],
      'Sedang Diproses'=> ['bg'=>'bg-blue-100',  'text'=>'text-blue-700',  'dot'=>'bg-blue-500',  'border'=>'border-blue-200'],
      'Selesai'        => ['bg'=>'bg-green-100', 'text'=>'text-green-700', 'dot'=>'bg-green-500', 'border'=>'border-gray-100'],
    ];
    $st = $statusConfig[$complaint->status_tiket] ?? $statusConfig['Open'];
    $isNew = $complaint->status_tiket === 'Open';
  @endphp
  <div class="bg-white rounded-3xl border {{ $st['border'] }} shadow-sm overflow-hidden {{ $isNew ? 'ring-2 ring-amber-100' : '' }}">
    <div class="p-5">
      {{-- Top Row --}}
      <div class="flex items-start justify-between gap-3 mb-4">
        <div class="min-w-0">
          <div class="flex items-center gap-2 mb-1.5">
            <span class="text-xs font-bold text-gray-400">#{{ $complaint->id }}</span>
            @if($isNew)
            <span class="bg-amber-500 text-white text-[9px] font-black px-2 py-0.5 rounded-full uppercase tracking-wide animate-pulse">Baru</span>
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
          <span class="w-1.5 h-1.5 {{ $st['dot'] }} rounded-full {{ $isNew ? 'animate-pulse' : '' }}"></span>
          {{ $complaint->status_tiket }}
        </span>
      </div>

      {{-- Kategori & Deskripsi --}}
      <div class="bg-gray-50 rounded-2xl p-4 mb-4">
        <div class="flex items-center gap-2 mb-2">
          <span class="bg-red-100 text-red-700 text-[11px] font-bold px-2.5 py-1 rounded-lg">{{ $complaint->kategori_masalah }}</span>
        </div>
        <p class="text-sm text-gray-600 leading-relaxed">{{ $complaint->deskripsi }}</p>
      </div>

      {{-- Foto Bukti --}}
      @if($complaint->foto_bukti)
      <div class="mb-4">
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Foto Bukti</p>
        <img src="{{ Storage::url($complaint->foto_bukti) }}"
             alt="Foto bukti"
             class="w-full max-h-48 object-cover rounded-2xl border border-gray-100 shadow-sm">
      </div>
      @endif

      {{-- Balasan Admin --}}
      @if($complaint->balasan_admin)
      <div class="bg-green-50 border border-green-200 rounded-2xl p-4">
        <div class="flex items-center gap-2 mb-1.5">
          <div class="w-5 h-5 bg-green-500 rounded-full flex items-center justify-center">
            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
          </div>
          <p class="text-xs font-black text-green-700 uppercase tracking-wide">Keputusan Tim Sisa Rasa</p>
        </div>
        <p class="text-sm text-green-800 leading-relaxed">{{ $complaint->balasan_admin }}</p>
      </div>
      @else
      <div class="bg-gray-50 border border-dashed border-gray-200 rounded-2xl p-3 text-center">
        <p class="text-xs text-gray-400">⏳ Menunggu keputusan Tim Sisa Rasa...</p>
      </div>
      @endif
    </div>
  </div>
  @endforeach
</div>
@endif

@endsection

@push('scripts')
<script>
function sellerPanel() {
  return {}
}
</script>
@endpush
