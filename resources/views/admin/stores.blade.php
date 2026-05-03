<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Validation Queue - Sisa Rasa Admin</title>
@vite(['resources/css/app.css', 'resources/js/app.js'])
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*{font-family:'Inter',sans-serif;}body{background:#F7F5F3;}
.sidebar-bar{position:absolute;right:-1px;top:50%;transform:translateY(-50%);width:4px;height:32px;background:#C0392B;border-radius:4px 0 0 4px;}
.card-bar{position:absolute;left:0;top:20%;height:60%;width:5px;background:#C0392B;border-radius:0 6px 6px 0;}
.tr{color:#C0392B;}.bg-tr{background:#C0392B;}
::-webkit-scrollbar{width:4px;}::-webkit-scrollbar-thumb{background:#E0DADA;border-radius:10px;}
</style>
</head>
<body class="overflow-hidden h-screen" x-data="adminPanel()">
<div class="flex h-screen">

{{-- SIDEBAR --}}
<aside class="w-60 bg-white flex flex-col border-r border-gray-100 flex-shrink-0">
  <div class="px-7 pt-8 pb-5">
    <p class="text-xl font-black tr tracking-tight">Sisa Rasa</p>
    <p class="text-[9px] font-semibold text-gray-400 tracking-[0.18em] uppercase mt-0.5">Admin Console</p>
  </div>
  <nav class="flex-1 px-3 space-y-0.5 mt-2">
    <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl tr font-semibold text-sm relative bg-red-50/40">
      <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
      Validation Queue<div class="sidebar-bar"></div>
    </a>
    <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 font-medium text-sm hover:text-gray-600 hover:bg-gray-50">
      <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>Stores
    </a>
    <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 font-medium text-sm hover:text-gray-600 hover:bg-gray-50">
      <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>Reports
    </a>
    <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 font-medium text-sm hover:text-gray-600 hover:bg-gray-50">
      <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>Settings
    </a>
  </nav>
  <div class="px-3 pb-8 space-y-0.5">
    <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 font-medium text-sm hover:text-gray-600 hover:bg-gray-50">
      <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Help
    </a>
    <form method="POST" action="{{ route('logout') }}">@csrf
      <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 font-medium text-sm hover:text-red-500 hover:bg-red-50">
        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>Logout
      </button>
    </form>
  </div>
</aside>

{{-- MAIN --}}
<div class="flex-1 flex flex-col overflow-hidden">
  {{-- TOPBAR --}}
  <header class="px-8 py-5 flex items-center gap-6">
    <div class="flex-1">
      <div class="relative max-w-lg">
        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="text" placeholder="Search validations, stores, or IDs..." class="w-full pl-10 pr-4 py-2.5 bg-white/80 border border-gray-200/60 rounded-full text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-100 shadow-sm">
      </div>
    </div>
    <div class="flex items-center gap-5">
      <button class="relative text-gray-400 hover:text-gray-600">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/></svg>
        <span class="absolute -top-0.5 -right-0.5 w-2 h-2 bg-red-500 rounded-full border border-white"></span>
      </button>
      <button class="text-gray-400 hover:text-gray-600">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </button>
      <div class="flex items-center gap-3 pl-4 border-l border-gray-200">
        <div class="text-right">
          <p class="text-sm font-semibold text-gray-800 leading-none">Alex Rivera</p>
          <p class="text-[10px] text-gray-400 mt-0.5">Super Admin</p>
        </div>
        <img src="https://ui-avatars.com/api/?name=Alex+Rivera&background=C0392B&color=fff&size=64" class="w-9 h-9 rounded-xl">
      </div>
    </div>
  </header>

  {{-- CONTENT --}}
  <main class="flex-1 overflow-hidden px-8 pb-8">
    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm font-medium">✓ {{ session('success') }}</div>
    @endif

    <div class="flex items-center gap-3 mb-5">
      <h1 class="text-3xl font-black text-gray-900 tracking-tight">Validation Queue</h1>
      <span class="bg-orange-100 text-orange-600 text-xs font-bold px-3 py-1 rounded-full">{{ $sellers->count() }} Total</span>
    </div>
    <div class="flex items-center gap-3 mb-6">
      <button class="bg-white shadow-md shadow-gray-200/60 text-gray-800 font-semibold text-sm px-5 py-2 rounded-full">New Registrations</button>
      <button class="text-gray-400 font-medium text-sm px-5 py-2 hover:text-gray-600">Profile Updates</button>
    </div>

    <div class="flex gap-6 h-[calc(100vh-260px)]">

      {{-- LEFT: Cards --}}
      <div class="w-[440px] flex-shrink-0 space-y-3 overflow-y-auto pr-1">
        @forelse($sellers as $seller)
        @php
          $vs = $seller->verification_status ?? 'pending';
          $badgeClass = ['pending'=>'bg-yellow-100 text-yellow-700','approved'=>'bg-green-100 text-green-700','rejected'=>'bg-red-100 text-red-700','suspended'=>'bg-gray-100 text-gray-600'][$vs] ?? 'bg-yellow-100 text-yellow-700';
          $hasPending = !empty($seller->pending_profile_updates);
        @endphp
        <div @click="select({{ json_encode(['id'=>$seller->id,'store_name'=>$seller->store_name,'address'=>$seller->address,'latitude'=>$seller->latitude,'longitude'=>$seller->longitude,'open_time'=>$seller->open_time,'close_time'=>$seller->close_time,'store_photo'=>$seller->store_photo,'verification_status'=>$vs]) }})"
          class="bg-white rounded-2xl cursor-pointer relative overflow-hidden transition-all duration-200 border-2 {{ $hasPending ? 'border-blue-200' : 'border-transparent' }} shadow-sm hover:shadow-md">
          <div x-show="selected && selected.id === {{ $seller->id }}" class="card-bar"></div>

          <div class="p-5">
            <div class="flex justify-between items-start gap-3 mb-1">
              <div class="flex items-center gap-2 min-w-0">
                <h3 class="font-bold text-gray-900 text-base leading-tight truncate">{{ $seller->store_name ?? 'Untitled Store' }}</h3>
                @if($hasPending)
                  <span class="flex-shrink-0 inline-flex items-center gap-1 bg-blue-100 text-blue-700 text-[9px] font-black px-2 py-0.5 rounded-full uppercase tracking-wide animate-pulse">
                    <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>PENDING UPDATE
                  </span>
                @endif
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
                  <p class="text-[9px] font-bold text-gray-300 uppercase tracking-widest mb-1">Date Submitted</p>
                  <p class="text-xs font-semibold text-gray-700">{{ $seller->created_at->format('M d, Y') }}</p>
                </div>
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

          {{-- ============================================================= --}}
          {{-- VISUAL DIFF COMPARATOR — hanya muncul bila ada pending update  --}}
          {{-- ============================================================= --}}
          @if($hasPending)
          @php $pending = $seller->pending_profile_updates; @endphp
          <div class="mx-3 mb-3 rounded-xl overflow-hidden border border-blue-200 bg-blue-50/60">

            {{-- Header Alert --}}
            <div class="flex items-center justify-between px-4 py-2.5 bg-blue-600">
              <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <p class="text-white text-xs font-black tracking-wide">⚠️ ADA PERMINTAAN PERUBAHAN PROFILE</p>
              </div>
              @if(isset($pending['requested_at']))
                <span class="text-blue-200 text-[10px] font-medium">{{ \Carbon\Carbon::parse($pending['requested_at'])->diffForHumans() }}</span>
              @endif
            </div>

            {{-- Diff Table --}}
            <div class="px-4 pt-3 pb-2">
              <div class="grid grid-cols-2 gap-x-3 mb-2">
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">📌 Data Lama (Aktif)</p>
                <p class="text-[9px] font-black text-blue-600 uppercase tracking-widest mb-1">✏️ Usulan Baru</p>
              </div>

              @php
                $diffFields = [
                  'store_name'    => 'Nama Toko',
                  'address'       => 'Alamat',
                  'open_time'     => 'Jam Buka',
                  'close_time'    => 'Jam Tutup',
                  'discount_time' => 'Jam Diskon',
                  'latitude'      => 'Latitude',
                  'longitude'     => 'Longitude',
                ];
              @endphp

              @foreach($diffFields as $field => $label)
                @php
                  $oldVal = $seller->$field ?? '—';
                  $newVal = $pending[$field] ?? '—';
                  $changed = $oldVal != $newVal;
                @endphp
                @if($changed)
                <div class="grid grid-cols-2 gap-x-3 py-1.5 border-t border-blue-100 first:border-0">
                  <div>
                    <p class="text-[9px] text-gray-400 font-semibold mb-0.5">{{ $label }}</p>
                    <p class="text-xs font-medium text-gray-600 bg-white/70 rounded px-1.5 py-0.5 line-through decoration-red-300">{{ $oldVal }}</p>
                  </div>
                  <div>
                    <p class="text-[9px] text-blue-500 font-semibold mb-0.5">{{ $label }}</p>
                    <p class="text-xs font-bold text-blue-800 bg-blue-100 rounded px-1.5 py-0.5">{{ $newVal }}</p>
                  </div>
                </div>
                @endif
              @endforeach
            </div>

            {{-- Action Buttons --}}
            <div class="flex gap-2 px-4 pb-3 pt-1" onclick="event.stopPropagation()">
              <form method="POST" action="{{ route('admin.sellers.approve-update', $seller->id) }}" class="flex-1">
                @csrf @method('PATCH')
                <button type="submit" class="w-full flex items-center justify-center gap-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-bold py-2 rounded-lg transition-colors shadow-sm">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                  Setujui Perubahan
                </button>
              </form>
              <form method="POST" action="{{ route('admin.sellers.reject-update', $seller->id) }}" class="flex-1">
                @csrf @method('PATCH')
                <button type="submit" class="w-full flex items-center justify-center gap-1.5 bg-white hover:bg-red-50 text-red-500 text-xs font-bold py-2 rounded-lg border border-red-200 transition-colors">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                  Tolak Usulan
                </button>
              </form>
            </div>
          </div>
          @endif

        </div>
        @empty
        <div class="bg-white rounded-2xl p-10 text-center text-gray-400 text-sm shadow-sm">Tidak ada data toko.</div>
        @endforelse
      </div>

      {{-- RIGHT: Detail --}}
      <div class="flex-1 overflow-y-auto">
        <div x-show="!selected" class="h-full flex flex-col items-center justify-center text-gray-300 gap-4 border-2 border-dashed border-gray-200 rounded-3xl">
          <svg class="w-16 h-16 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5"/></svg>
          <p class="font-semibold text-gray-400">Pilih toko untuk melihat detail</p>
        </div>

        <div x-show="selected" x-transition class="bg-white rounded-3xl shadow-xl overflow-hidden">
          <div class="relative h-44 bg-gray-100">
            <template x-if="selected && selected.store_photo">
              <img :src="'/storage/' + selected.store_photo" class="w-full h-full object-cover">
            </template>
            <template x-if="!selected || !selected.store_photo">
              <div class="w-full h-full bg-gradient-to-br from-teal-700 to-teal-900 flex items-center justify-center">
                <svg class="w-16 h-16 text-teal-400 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
              </div>
            </template>
            <span class="absolute top-4 left-4 bg-teal-500 text-white text-[9px] font-bold px-3 py-1.5 rounded-lg uppercase tracking-widest">Verified Business Entity</span>
          </div>
          <div class="p-6">
            <h2 class="text-2xl font-black text-gray-900 mb-5 leading-tight" x-text="selected ? selected.store_name : ''"></h2>
            <div class="grid grid-cols-2 gap-5 mb-5">
              <div>
                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Address</p>
                <p class="text-xs text-gray-700 font-medium leading-relaxed" x-text="selected ? selected.address : '-'"></p>
              </div>
              <div>
                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Coordinates</p>
                <p class="text-xs text-gray-700 font-medium" x-text="selected && selected.latitude ? selected.latitude + '° N' : '-'"></p>
                <p class="text-xs text-gray-700 font-medium" x-text="selected && selected.longitude ? selected.longitude + '° W' : ''"></p>
              </div>
            </div>
            <div class="mb-6">
              <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-2">Operating Hours</p>
              <div class="flex justify-between text-xs text-gray-700 font-medium">
                <span x-text="selected ? 'Mon-Fri: ' + (selected.open_time ? selected.open_time.substring(0,5) : '08:00') + ' - ' + (selected.close_time ? selected.close_time.substring(0,5) : '20:00') : ''"></span>
                <span>Sat: 09:00 - 18:00</span>
              </div>
              <p class="text-xs text-gray-700 font-medium mt-1">Sun: Closed</p>
            </div>

            {{-- 3 ACTION BUTTONS --}}
            <div class="flex gap-2 mb-4">
              {{-- APPROVE --}}
              <form method="POST" class="flex-1" :action="selected ? '/admin/sellers/' + selected.id + '/verify' : '#'">
                @csrf @method('PATCH')
                <input type="hidden" name="status_action" value="approved">
                <button type="submit" class="w-full flex items-center justify-center gap-1.5 bg-green-700 hover:bg-green-800 text-white font-bold py-3 rounded-xl text-sm shadow-md shadow-green-200 transition-colors">
                  <span class="w-2 h-2 bg-green-300 rounded-full"></span> Approve
                </button>
              </form>
              {{-- SUSPEND --}}
              <button @click="openModal('suspended')" class="flex-1 flex items-center justify-center gap-1.5 bg-yellow-400 hover:bg-yellow-500 text-white font-bold py-3 rounded-xl text-sm shadow-md shadow-yellow-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Suspend
              </button>
              {{-- REJECT --}}
              <button @click="openModal('rejected')" class="flex-1 flex items-center justify-center gap-1.5 bg-white hover:bg-red-50 text-red-500 font-bold py-3 rounded-xl text-sm border-2 border-red-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                Reject
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

{{-- MODAL REJECT / SUSPEND --}}
<div x-show="showModal" x-transition class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-6" @click.self="showModal=false">
  <div x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
    class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8">
    <div class="flex items-center justify-between mb-2">
      <h3 class="text-lg font-bold text-gray-900" x-text="modalAction === 'rejected' ? '❌ Tolak Toko' : '⏸ Bekukan Toko'"></h3>
      <button @click="showModal=false" class="text-gray-400 hover:text-gray-600">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <p class="text-sm text-gray-500 mb-5" x-text="'Toko: ' + (selected ? selected.store_name : '')"></p>
    <form method="POST" :action="selected ? '/admin/sellers/' + selected.id + '/verify' : '#'">
      @csrf @method('PATCH')
      <input type="hidden" name="status_action" :value="modalAction">
      <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Alasan <span x-text="modalAction === 'rejected' ? 'Penolakan' : 'Penangguhan'"></span> <span class="text-red-400">*</span></label>
      <textarea name="rejection_reason" rows="4" required
        :placeholder="modalAction === 'rejected' ? 'e.g., Dokumen tidak valid, foto toko tidak jelas...' : 'e.g., Ditemukan pelanggaran kebijakan platform...'"
        class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3 text-sm text-gray-600 placeholder-gray-300 resize-none focus:ring-2 focus:outline-none mb-5"
        :class="modalAction==='rejected' ? 'focus:ring-red-100' : 'focus:ring-yellow-100'"></textarea>
      <p class="text-[10px] text-gray-400 italic mb-5">Pesan ini akan dikirimkan ke pemilik toko.</p>
      <div class="flex gap-3">
        <button type="button" @click="showModal=false" class="flex-1 py-3 rounded-xl border-2 border-gray-200 text-gray-600 font-semibold text-sm hover:bg-gray-50">Batal</button>
        <button type="submit" class="flex-1 py-3 rounded-xl font-bold text-sm text-white transition-colors"
          :class="modalAction==='rejected' ? 'bg-red-500 hover:bg-red-600 shadow-lg shadow-red-100' : 'bg-yellow-400 hover:bg-yellow-500 shadow-lg shadow-yellow-100'">
          <span x-text="modalAction==='rejected' ? 'Konfirmasi Penolakan' : 'Bekukan Akun'"></span>
        </button>
      </div>
    </form>
  </div>
</div>

{{-- FAB --}}
<div class="fixed bottom-8 right-8">
  <button class="bg-tr w-14 h-14 rounded-full shadow-xl shadow-red-200 text-white text-2xl font-light flex items-center justify-center hover:scale-110 transition-transform">+</button>
</div>

<script>
function adminPanel() {
  return {
    selected: null,
    showModal: false,
    modalAction: 'rejected',
    select(seller) { this.selected = seller; },
    openModal(action) { this.modalAction = action; this.showModal = true; }
  }
}
</script>
</body>
</html>
