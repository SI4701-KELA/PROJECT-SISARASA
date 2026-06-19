@extends('layouts.admin')

@section('title', 'Laporan Pembeli')

@section('content')
<div class="flex items-center gap-3 mb-5">
  <h1 class="text-3xl font-black text-gray-900 tracking-tight">Daftar Laporan Pembeli</h1>
  <span class="bg-red-100 text-red-600 text-xs font-bold px-3 py-1 rounded-full">{{ $reports->count() }} Laporan</span>
</div>

<div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden" x-data="{ showModal: false, selectedReport: null }">
  <div class="overflow-x-auto">
    <table class="w-full text-left text-sm text-gray-500">
      <thead class="text-xs text-gray-400 uppercase bg-gray-50/50 font-bold tracking-wider border-b border-gray-100">
        <tr>
          <th scope="col" class="px-6 py-4">Tanggal</th>
          <th scope="col" class="px-6 py-4">Pelapor (Buyer)</th>
          <th scope="col" class="px-6 py-4">Terlapor (Toko)</th>
          <th scope="col" class="px-6 py-4">Kategori & Bukti</th>
          <th scope="col" class="px-6 py-4">Deskripsi</th>
          <th scope="col" class="px-6 py-4">Status</th>
          <th scope="col" class="px-6 py-4 text-right">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @forelse($reports as $report)
        <tr class="hover:bg-gray-50/50 transition-colors">
          <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
            {{ $report->created_at->format('d M Y') }}
            <p class="text-[10px] text-gray-400 font-semibold mt-0.5">{{ $report->created_at->format('H:i') }}</p>
          </td>
          <td class="px-6 py-4">
            <span class="font-bold text-gray-700">{{ $report->buyer->name ?? 'Unknown' }}</span>
          </td>
          <td class="px-6 py-4">
            <span class="font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded-md">{{ $report->seller->store_name ?? 'Unknown Store' }}</span>
          </td>
          <td class="px-6 py-4">
            <div class="flex flex-col items-start gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold bg-orange-50 text-orange-700 border border-orange-100">
                {{ $report->kategori }}
                </span>
                @if($report->foto_bukti)
                <a href="{{ asset('storage/' . $report->foto_bukti) }}" target="_blank" class="flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 px-2 py-1 rounded-md transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Lihat Bukti
                </a>
                @else
                <p class="text-[10px] text-gray-400 font-medium italic">Tidak ada foto</p>
                @endif
            </div>
          </td>
          <td class="px-6 py-4">
            <p class="text-xs text-gray-600 max-w-xs break-words line-clamp-3 leading-relaxed" title="{{ $report->deskripsi }}">
              {{ $report->deskripsi }}
            </p>
          </td>
          <td class="px-6 py-4">
            @php
              $statusColors = [
                'Pending' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                'Diproses' => 'bg-blue-100 text-blue-700 border-blue-200',
                'Selesai' => 'bg-green-100 text-green-700 border-green-200',
                'Ditolak' => 'bg-gray-100 text-gray-600 border-gray-200',
              ];
              $color = $statusColors[$report->status] ?? 'bg-gray-100 text-gray-600 border-gray-200';
            @endphp
            <span class="px-2.5 py-1 text-[10px] font-bold rounded-full uppercase tracking-wider border {{ $color }}">
              {{ $report->status }}
            </span>
          </td>
          <td class="px-6 py-4 text-right align-middle">
            {{-- Tombol Tindak Lanjut --}}
            @php
              $isBanned = $report->seller->user->is_banned ?? false;
              $isResolved = in_array($report->status, ['Selesai', 'Ditolak']);
              $isDisabled = $isBanned || $isResolved;
            @endphp
            
            @if($isDisabled)
            <button disabled class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-gray-100 text-gray-400 text-xs font-bold rounded-xl border border-gray-200 cursor-not-allowed">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
              Tindak Lanjuti
            </button>
            @else
            <button @click="selectedReport = { id: {{ $report->id }}, seller: '{{ addslashes($report->seller->store_name ?? 'Unknown Store') }}' }; showModal = true;" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-white hover:bg-gray-50 text-gray-700 text-xs font-bold rounded-xl border border-gray-200 shadow-sm transition-all hover:shadow">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
              Tindak Lanjuti
            </button>
            @endif
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" class="px-6 py-16 text-center text-gray-400 font-medium">
            <div class="flex flex-col items-center justify-center">
              <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              </div>
              <p class="text-gray-500 font-semibold mb-1">Tidak ada laporan yang masuk</p>
              <p class="text-xs text-gray-400">Seluruh laporan dari pembeli akan muncul di tabel ini.</p>
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- MODAL TINDAK LANJUT --}}
  <div x-show="showModal" x-transition class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-6" style="display: none;">
    <div @click.away="showModal = false" class="bg-white rounded-3xl w-full max-w-md p-8 shadow-2xl relative">
      <button @click="showModal = false" class="absolute top-6 right-6 text-gray-400 hover:text-gray-600">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
      <div class="mb-6">
        <h3 class="text-xl font-bold text-gray-900 mb-1">Tindak Lanjuti Laporan</h3>
        <p class="text-sm text-gray-500">Pilih tindakan untuk toko <span x-text="selectedReport?.seller" class="font-bold text-terracotta"></span></p>
      </div>

      <div class="flex gap-4">
        {{-- BUTTON TOLAK LAPORAN --}}
        <form :action="'/admin/reports/' + selectedReport?.id + '/reject'" method="POST" class="flex-1">
          @csrf @method('PATCH')
          <button type="submit" class="w-full flex flex-col items-center justify-center gap-2 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 p-4 rounded-2xl transition-colors">
            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-sm font-bold">Tolak Laporan</span>
            <span class="text-[9px] text-gray-400 text-center font-medium">Laporan palsu/tidak valid.</span>
          </button>
        </form>

        {{-- BUTTON BLOKIR TOKO --}}
        <form :action="'/admin/reports/' + selectedReport?.id + '/ban-store'" method="POST" class="flex-1" @submit="if(!confirm('⚠️ YAKIN INGIN MEMBLOKIR TOKO INI PERMANEN?\n\nAkun penjual ini akan dinonaktifkan dan toko akan hilang dari aplikasi.')) { $event.preventDefault(); }">
          @csrf @method('PATCH')
          <button type="submit" class="w-full flex flex-col items-center justify-center gap-2 bg-red-50 hover:bg-red-100 border border-red-200 text-red-600 p-4 rounded-2xl transition-colors shadow-sm">
            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span class="text-sm font-bold">Blokir Toko</span>
            <span class="text-[9px] text-red-400 text-center font-medium">Nonaktifkan toko permanen.</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
