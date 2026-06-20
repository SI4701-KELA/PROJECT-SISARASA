@extends('layouts.admin')

@section('title', 'Daftar Toko Mitra')

@section('content')
@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm font-medium">✓ {{ session('success') }}</div>
@endif

<div class="flex items-center gap-3 mb-5">
  <h1 class="text-3xl font-black text-gray-900 tracking-tight">Daftar Toko Mitra</h1>
  <span class="bg-blue-100 text-blue-600 text-xs font-bold px-3 py-1 rounded-full">{{ $sellers->count() }} Total Toko</span>
</div>

<div class="flex gap-6 h-[calc(100vh-200px)]">

  {{-- LEFT: Cards --}}
  <div class="w-[440px] flex-shrink-0 space-y-3 overflow-y-auto pr-1">
    @forelse($sellers as $seller)
    @php
      $vs = $seller->verification_status ?? 'pending';
      $badgeClass = ['pending'=>'bg-yellow-100 text-yellow-700','approved'=>'bg-green-100 text-green-700','rejected'=>'bg-red-100 text-red-700','suspended'=>'bg-gray-100 text-gray-600'][$vs] ?? 'bg-yellow-100 text-yellow-700';
    @endphp
    
    <div @click="select({{ json_encode(['id'=>$seller->id,'store_name'=>$seller->store_name,'address'=>$seller->address,'latitude'=>$seller->latitude,'longitude'=>$seller->longitude,'open_time'=>$seller->open_time,'close_time'=>$seller->close_time,'store_photo'=>$seller->store_photo,'verification_status'=>$vs]) }})"
      class="bg-white rounded-2xl cursor-pointer relative overflow-hidden transition-all duration-200 border-2 border-transparent shadow-sm hover:shadow-md">
      <div x-show="selected && selected.id === {{ $seller->id }}" class="card-bar"></div>

      <div class="p-5">
        <div class="flex justify-between items-start gap-3 mb-1">
          <div class="flex items-center gap-2 min-w-0">
            <h3 class="font-bold text-gray-900 text-base leading-tight truncate">{{ $seller->store_name ?? 'Untitled Store' }}</h3>
          </div>
          <span class="text-[10px] font-bold px-2.5 py-1 rounded-full flex-shrink-0 uppercase tracking-wide {{ $badgeClass }}">{{ $vs }}</span>
        </div>
        <p class="text-gray-400 text-xs flex items-center gap-1 mb-4">
          <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
          {{ $seller->address ?? 'Location not set' }} • Registered {{ $seller->created_at->diffForHumans() }}
        </p>
        <div class="flex items-end justify-between">
          <div class="flex gap-8">
            <div>
              <p class="text-[9px] font-bold text-gray-300 uppercase tracking-widest mb-1">Status</p>
              <p class="text-xs font-bold {{ ['pending'=>'text-orange-500','approved'=>'text-green-600','rejected'=>'text-red-500','suspended'=>'text-gray-500'][$vs] ?? 'text-orange-500' }}">
                {{ ucfirst($vs) }}
              </p>
            </div>
          </div>
          <div class="flex items-center gap-1 text-gray-400 text-xs font-medium hover:text-red-500">
            Review Details <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
          </div>
        </div>
      </div>
    </div>
    @empty
    <div class="bg-white rounded-2xl p-10 text-center text-gray-400 text-sm shadow-sm border border-dashed border-gray-200">
      Belum ada toko yang terdaftar.
    </div>
    @endforelse
  </div>

  {{-- RIGHT: Detail --}}
  <div class="flex-1 overflow-y-auto">
    <div x-show="!selected" class="h-full flex flex-col items-center justify-center text-gray-300 gap-4 bg-white/50 rounded-3xl">
      <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center border-4 border-white shadow-sm mb-2">
        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      </div>
      <h3 class="text-xl font-bold text-gray-900">Pilih Toko</h3>
      <p class="text-sm font-medium text-gray-500 text-center max-w-xs">Pilih toko dari daftar di sebelah kiri untuk melihat detailnya.</p>
    </div>

    <div x-show="selected" x-transition class="bg-white rounded-3xl shadow-xl overflow-hidden" style="display: none;">
      <div class="relative h-44 bg-gray-100">
        <template x-if="selected && selected.store_photo">
          <img :src="'/storage/' + selected.store_photo" class="w-full h-full object-cover">
        </template>
        <template x-if="!selected || !selected.store_photo">
          <div class="w-full h-full bg-gradient-to-br from-teal-700 to-teal-900 flex items-center justify-center">
            <svg class="w-16 h-16 text-teal-400 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          </div>
        </template>
      </div>
      <div class="p-6">
        <h2 class="text-2xl font-black text-gray-900 mb-5 leading-tight" x-text="selected ? selected.store_name : ''"></h2>
        <div class="grid grid-cols-2 gap-5 mb-5">
          <div>
            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Alamat</p>
            <p class="text-xs text-gray-700 font-medium leading-relaxed" x-text="selected ? selected.address : '-'"></p>
          </div>
          <div>
            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Koordinat</p>
            <p class="text-xs text-gray-700 font-medium" x-text="selected && selected.latitude ? selected.latitude + '° N' : '-'"></p>
            <p class="text-xs text-gray-700 font-medium" x-text="selected && selected.longitude ? selected.longitude + '° W' : ''"></p>
          </div>
        </div>
        <div class="mb-6">
          <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-2">Jam Operasional</p>
          <div class="flex justify-between text-xs text-gray-700 font-medium">
            <span x-text="selected ? 'Sen-Jum: ' + (selected.open_time ? selected.open_time.substring(0,5) : '08:00') + ' - ' + (selected.close_time ? selected.close_time.substring(0,5) : '20:00') : ''"></span>
          </div>
        </div>

        {{-- ACTION BUTTONS --}}
        <div class="flex gap-2 mb-4">
          <template x-if="selected && selected.verification_status !== 'approved'">
            <form method="POST" class="flex-1" :action="selected ? '/admin/sellers/' + selected.id + '/verify' : '#'">
              @csrf @method('PATCH')
              <input type="hidden" name="status_action" value="approved">
              <button type="submit" class="w-full flex items-center justify-center gap-1.5 bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl text-sm shadow-md transition-colors">
                Approve
              </button>
            </form>
          </template>
          
          <template x-if="selected && selected.verification_status !== 'suspended'">
            <button @click="openModal('suspended')" class="flex-1 flex items-center justify-center gap-1.5 bg-yellow-400 hover:bg-yellow-500 text-white font-bold py-3 rounded-xl text-sm shadow-md transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              Suspend
            </button>
          </template>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
{{-- MODAL SUSPEND --}}
<div x-show="showModal" x-transition class="fixed inset-0 bg-black/40 backdrop-blur-sm z-[100] flex items-center justify-center p-6" @click.self="showModal=false" style="display: none;">
  <div x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
    class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8">
    <div class="flex items-center justify-between mb-2">
      <h3 class="text-lg font-bold text-gray-900">⏸ Bekukan Toko</h3>
      <button @click="showModal=false" class="text-gray-400 hover:text-gray-600">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <p class="text-sm text-gray-500 mb-5" x-text="'Toko: ' + (selected ? selected.store_name : '')"></p>
    <form method="POST" :action="selected ? '/admin/sellers/' + selected.id + '/verify' : '#'">
      @csrf @method('PATCH')
      <input type="hidden" name="status_action" :value="modalAction">
      <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Alasan Penangguhan <span class="text-red-400">*</span></label>
      <textarea name="rejection_reason" rows="4" required
        placeholder="e.g., Ditemukan pelanggaran kebijakan platform..."
        class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3 text-sm text-gray-600 placeholder-gray-300 resize-none focus:ring-2 focus:outline-none mb-5 focus:ring-yellow-100"></textarea>
      <p class="text-[10px] text-gray-400 italic mb-5">Pesan ini akan dikirimkan ke pemilik toko.</p>
      <div class="flex gap-3">
        <button type="button" @click="showModal=false" class="flex-1 py-3 rounded-xl border-2 border-gray-200 text-gray-600 font-semibold text-sm hover:bg-gray-50">Batal</button>
        <button type="submit" class="flex-1 py-3 rounded-xl font-bold text-sm text-white transition-colors bg-yellow-400 hover:bg-yellow-500 shadow-lg shadow-yellow-100">
          Bekukan Akun
        </button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  if(typeof adminPanel === 'undefined'){
    window.adminPanel = function() {
      return {
        selected: null,
        showModal: false,
        modalAction: 'suspended',
        select(seller) { this.selected = seller; },
        openModal(action) { this.modalAction = action; this.showModal = true; }
      }
    }
  }
});
</script>
@endpush
