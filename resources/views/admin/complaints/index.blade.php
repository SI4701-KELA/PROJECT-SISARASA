@extends('layouts.admin')

@section('title', 'Manajemen Komplain')

@section('content')

{{-- Flash --}}
@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm font-medium flex items-center gap-2">
  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
  {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm font-medium">⚠️ {{ session('error') }}</div>
@endif

<div x-data="complaintsPanel()" class="h-full">

  {{-- Page Header --}}
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-3xl font-black text-gray-900 tracking-tight">Manajemen Komplain</h1>
      <p class="text-sm text-gray-500 mt-1">Tinjauan dan balasan tiket komplain dari pembeli</p>
    </div>
    <div class="flex items-center gap-3">
      @php
        $openCount = $complaints->where('status_tiket', 'Open')->count();
        $prosesCount = $complaints->where('status_tiket', 'Sedang Diproses')->count();
      @endphp
      @if($openCount)
      <span class="bg-amber-100 text-amber-700 text-xs font-black px-3 py-1.5 rounded-full">{{ $openCount }} Open</span>
      @endif
      @if($prosesCount)
      <span class="bg-blue-100 text-blue-700 text-xs font-black px-3 py-1.5 rounded-full">{{ $prosesCount }} Diproses</span>
      @endif
    </div>
  </div>

  {{-- Table --}}
  <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
    @if($complaints->isEmpty())
    <div class="py-20 text-center">
      <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
      </div>
      <p class="text-gray-500 font-semibold">Tidak ada tiket komplain saat ini</p>
      <p class="text-gray-400 text-sm mt-1">Komplain dari pembeli akan muncul di sini</p>
    </div>
    @else
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b border-gray-100 bg-gray-50/50">
          <th class="text-left px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">ID</th>
          <th class="text-left px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Pembeli</th>
          <th class="text-left px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Toko</th>
          <th class="text-left px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Kategori</th>
          <th class="text-left px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
          <th class="text-left px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
          <th class="px-6 py-4"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-50">
        @foreach($complaints as $complaint)
        @php
          $statusConfig = [
            'Open'           => ['bg'=>'bg-amber-100', 'text'=>'text-amber-700', 'dot'=>'bg-amber-400'],
            'Sedang Diproses'=> ['bg'=>'bg-blue-100',  'text'=>'text-blue-700',  'dot'=>'bg-blue-500'],
            'Selesai'        => ['bg'=>'bg-green-100', 'text'=>'text-green-700', 'dot'=>'bg-green-500'],
          ];
          $st = $statusConfig[$complaint->status_tiket] ?? $statusConfig['Open'];
          $isDone = $complaint->status_tiket === 'Selesai';
        @endphp
        <tr class="hover:bg-gray-50/50 transition-colors {{ $isDone ? 'opacity-70' : '' }}">
          <td class="px-6 py-4">
            <span class="font-black text-gray-400 text-xs">#{{ $complaint->id }}</span>
          </td>
          <td class="px-6 py-4">
            <p class="font-semibold text-gray-800 text-xs">{{ $complaint->buyer->name ?? '-' }}</p>
            <p class="text-gray-400 text-[11px]">{{ $complaint->buyer->email ?? '' }}</p>
          </td>
          <td class="px-6 py-4">
            <p class="font-semibold text-gray-800 text-xs truncate max-w-[160px]">{{ $complaint->seller->store_name ?? '-' }}</p>
          </td>
          <td class="px-6 py-4">
            <span class="bg-gray-100 text-gray-600 text-[11px] font-semibold px-2.5 py-1 rounded-lg">{{ $complaint->kategori_masalah }}</span>
          </td>
          <td class="px-6 py-4">
            <span class="inline-flex items-center gap-1.5 {{ $st['bg'] }} {{ $st['text'] }} text-[11px] font-bold px-2.5 py-1 rounded-full">
              <span class="w-1.5 h-1.5 {{ $st['dot'] }} rounded-full {{ !$isDone ? 'animate-pulse' : '' }}"></span>
              {{ $complaint->status_tiket }}
            </span>
          </td>
          <td class="px-6 py-4 text-gray-400 text-xs">{{ $complaint->created_at->format('d M Y') }}</td>
          <td class="px-6 py-4">
            <button
              @click="openModal({{ json_encode([
                'id'             => $complaint->id,
                'buyer_name'     => $complaint->buyer->name ?? '-',
                'buyer_email'    => $complaint->buyer->email ?? '',
                'seller_name'    => $complaint->seller->store_name ?? '-',
                'seller_address' => $complaint->seller->address ?? '-',
                'kategori'       => $complaint->kategori_masalah,
                'deskripsi'      => $complaint->deskripsi,
                'foto_bukti'     => $complaint->foto_bukti,
                'balasan_admin'  => $complaint->balasan_admin,
                'status_tiket'   => $complaint->status_tiket,
                'created_at'     => $complaint->created_at->format('d M Y H:i'),
                'is_done'        => $isDone,
                'buyer_user_id'  => $complaint->buyer_id,
                'seller_user_id' => $complaint->seller->user_id ?? null,
                'seller_action'  => $complaint->seller_action,
                'seller_reason'  => $complaint->seller_reason,
                'seller_proof'   => $complaint->seller_proof_path,
              ]) }})"
              class="text-xs font-bold px-4 py-2 rounded-xl transition-colors
                {{ $isDone
                  ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                  : 'bg-gray-900 hover:bg-gray-700 text-white cursor-pointer' }}">
              {{ $isDone ? 'Selesai' : 'Tinjau' }}
            </button>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @endif
  </div>

  {{-- ================================ --}}
  {{-- MODAL ALPINE.JS                  --}}
  {{-- ================================ --}}
  <div x-show="showModal" x-transition
    class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
    @click.self="showModal=false" style="display:none;">

    <div x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">

      {{-- Modal Header --}}
      <div class="flex items-center justify-between p-6 border-b border-gray-100">
        <div>
          <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Tiket Komplain</p>
          <h3 class="text-xl font-black text-gray-900 mt-0.5" x-text="'#' + (ticket ? ticket.id : '')"></h3>
        </div>
        <button @click="showModal=false" class="w-9 h-9 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors">
          <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>

      <div class="p-6 space-y-5">

        {{-- Info Dua Kolom --}}
        <div class="grid grid-cols-2 gap-4">
          <div class="bg-gray-50 rounded-2xl p-4">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">👤 Pembeli</p>
            <p class="font-bold text-gray-800 text-sm" x-text="ticket ? ticket.buyer_name : ''"></p>
            <p class="text-gray-500 text-xs" x-text="ticket ? ticket.buyer_email : ''"></p>
          </div>
          <div class="bg-gray-50 rounded-2xl p-4">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">🏪 Toko</p>
            <p class="font-bold text-gray-800 text-sm" x-text="ticket ? ticket.seller_name : ''"></p>
            <p class="text-gray-500 text-xs" x-text="ticket ? ticket.seller_address : ''"></p>
          </div>
        </div>

        {{-- Kategori & Deskripsi --}}
        <div>
          <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Kategori Masalah</p>
          <span class="bg-red-100 text-red-700 text-xs font-bold px-3 py-1.5 rounded-xl" x-text="ticket ? ticket.kategori : ''"></span>
        </div>

        <div>
          <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Deskripsi Keluhan</p>
          <div class="bg-gray-50 rounded-2xl p-4">
            <p class="text-sm text-gray-700 leading-relaxed" x-text="ticket ? ticket.deskripsi : ''"></p>
          </div>
        </div>

        {{-- Foto Bukti --}}
        <div x-show="ticket && ticket.foto_bukti">
          <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Foto Bukti</p>
          <img :src="ticket && ticket.foto_bukti ? '/storage/' + ticket.foto_bukti : ''"
               alt="Foto bukti"
               class="w-full max-h-60 object-cover rounded-2xl border border-gray-200 shadow-sm">
        </div>

        {{-- Sanggahan Seller --}}
        <div x-show="ticket && ticket.seller_action">
          <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Sanggahan / Respons Penjual</p>
          <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-3">
            <p class="text-xs font-black text-red-700 uppercase tracking-wide mb-1" x-text="ticket && ticket.seller_action === 'approved' ? 'Setuju' : 'Tolak & Sanggah'"></p>
            <p class="text-sm text-red-800 leading-relaxed" x-text="ticket ? ticket.seller_reason : ''"></p>
          </div>
          <div x-show="ticket && ticket.seller_proof">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Foto Bukti Penjual</p>
            <img :src="ticket && ticket.seller_proof ? '/storage/' + ticket.seller_proof : ''"
                 alt="Foto bukti penjual"
                 class="w-full max-h-60 object-cover rounded-2xl border border-gray-200 shadow-sm">
          </div>
        </div>

        {{-- Balasan Sebelumnya --}}
        <div x-show="ticket && ticket.balasan_admin">
          <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Balasan Sebelumnya</p>
          <div class="bg-green-50 border border-green-200 rounded-2xl p-4">
            <p class="text-sm text-green-800 leading-relaxed" x-text="ticket ? ticket.balasan_admin : ''"></p>
          </div>
        </div>

        {{-- Form Balasan (Hanya jika belum Selesai) --}}
        <div x-show="ticket && !ticket.is_done" x-transition>
          <div class="border-t border-gray-100 pt-5">
            {{-- Private Chat Buttons for Admin --}}
            <div class="grid grid-cols-2 gap-3 mb-5">
              <a :href="'/chat/' + ticket.buyer_user_id"
                 class="flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-3 rounded-2xl transition-colors shadow-sm">
                 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                 Hubungi Pembeli (Privat)
              </a>
              <a :href="'/chat/' + ticket.seller_user_id"
                 class="flex items-center justify-center gap-2 bg-[#2aab7f] hover:bg-[#239970] text-white font-bold text-xs py-3 rounded-2xl transition-colors shadow-sm">
                 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                 Hubungi Penjual (Privat)
              </a>
            </div>

            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">✏️ Tulis Balasan & Perbarui Status</p>
            <form method="POST" :action="ticket ? '/admin/complaints/' + ticket.id : '#'">
              @csrf
              @method('PATCH')

              <div class="mb-4">
                <label class="block text-xs font-bold text-gray-600 mb-2">Balasan Admin <span class="text-red-500">*</span></label>
                <textarea name="balasan_admin" rows="4" required minlength="10"
                  placeholder="Tulis resolusi atau penjelasan untuk pembeli..."
                  class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3 text-sm text-gray-700 placeholder-gray-300 resize-none focus:outline-none focus:ring-2 focus:ring-teal-200 focus:border-teal-300 transition-all"
                  x-text="ticket ? (ticket.balasan_admin || '') : ''"></textarea>
              </div>

              <div class="mb-5">
                <label class="block text-xs font-bold text-gray-600 mb-2">Status Tiket <span class="text-red-500">*</span></label>
                <select name="status_tiket" required
                  class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-200 appearance-none cursor-pointer">
                  <option value="Open" :selected="ticket && ticket.status_tiket === 'Open'">🟡 Open</option>
                  <option value="Sedang Diproses" :selected="ticket && ticket.status_tiket === 'Sedang Diproses'">🔵 Sedang Diproses</option>
                  <option value="Selesai" :selected="ticket && ticket.status_tiket === 'Selesai'">🟢 Selesai</option>
                </select>
                <p class="text-[11px] text-amber-600 mt-1.5 font-medium">⚠️ Tiket yang diubah ke "Selesai" tidak dapat diubah lagi.</p>
              </div>

              <div class="flex gap-3">
                <button type="button" @click="showModal=false"
                  class="flex-1 py-3 rounded-2xl border-2 border-gray-200 text-gray-600 font-semibold text-sm hover:bg-gray-50 transition-colors">
                  Batal
                </button>
                <button type="submit"
                  class="flex-1 py-3 rounded-2xl bg-gray-900 hover:bg-gray-700 text-white font-bold text-sm transition-colors shadow-lg">
                  Simpan & Perbarui
                </button>
              </div>
            </form>
          </div>
        </div>

        {{-- Locked State (Tiket Selesai) --}}
        <div x-show="ticket && ticket.is_done" x-transition>
          <div class="border-t border-gray-100 pt-5">
            <div class="bg-green-50 border border-green-200 rounded-2xl p-5 text-center">
              <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
              </div>
              <p class="font-black text-green-700 text-sm">Tiket Telah Selesai</p>
              <p class="text-green-600 text-xs mt-1">Tiket ini sudah ditandai selesai dan tidak dapat diubah lagi. (TC-CMP-007)</p>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

</div>

@endsection

@push('scripts')
<script>
function complaintsPanel() {
  return {
    showModal: false,
    ticket: null,
    openModal(ticketData) {
      this.ticket = ticketData;
      this.showModal = true;
    }
  }
}
</script>
@endpush
