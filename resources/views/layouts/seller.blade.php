<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Seller Console') - Sisa Rasa</title>
@vite(['resources/css/app.css', 'resources/js/app.js'])
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*{font-family:'Inter',sans-serif;}body{background:#F7F5F3;}
.sidebar-bar{position:absolute;right:-1px;top:50%;transform:translateY(-50%);width:4px;height:32px;background:#C0392B;border-radius:4px 0 0 4px;}
.card-bar{position:absolute;left:0;top:20%;height:60%;width:5px;background:#C0392B;border-radius:0 6px 6px 0;}
.tr{color:#C0392B;}.bg-tr{background:#C0392B;}
::-webkit-scrollbar{width:4px;}::-webkit-scrollbar-thumb{background:#E0DADA;border-radius:10px;}
</style>
@stack('styles')
</head>
<body class="overflow-hidden h-screen" x-data="sellerPanel()">
<div class="flex h-screen">

{{-- SIDEBAR --}}
<aside class="w-60 bg-white flex flex-col border-r border-gray-100 flex-shrink-0">
  <div class="px-7 pt-8 pb-5">
    <p class="text-xl font-black tr tracking-tight">Sisa Rasa</p>
    <p class="text-[9px] font-semibold text-gray-400 tracking-[0.18em] uppercase mt-0.5">Seller Console</p>
  </div>
  <nav class="flex-1 px-3 space-y-0.5 mt-2 overflow-y-auto">
    {{-- Profile Toko --}}
    <a href="{{ route('seller.profile') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-sm relative {{ request()->routeIs('seller.profile') ? 'tr bg-red-50/40' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50' }}">
      <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
      Profile Toko
      @if(request()->routeIs('seller.profile'))
        <div class="sidebar-bar"></div>
      @endif
    </a>
    {{-- Katalog Produk --}}
    <a href="{{ route('seller.products') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm relative {{ request()->routeIs('seller.products') ? 'tr font-semibold bg-red-50/40' : 'text-gray-400 font-medium hover:text-gray-600 hover:bg-gray-50' }}">
      <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
      Katalog Produk
      @if(request()->routeIs('seller.products'))
        <div class="sidebar-bar"></div>
      @endif
    </a>
    {{-- Daftar Pesanan --}}
    <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 font-medium text-sm hover:text-gray-600 hover:bg-gray-50">
      <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>Daftar Pesanan
    </a>
    {{-- Review & Ulasan --}}
    <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 font-medium text-sm hover:text-gray-600 hover:bg-gray-50">
      <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>Review & Ulasan
    </a>
    {{-- Dashboard Analitik --}}
    <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 font-medium text-sm hover:text-gray-600 hover:bg-gray-50">
      <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>Dashboard Analitik
    </a>
  </nav>

  <div class="px-3 pb-8 space-y-0.5 mt-4">
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
        <input type="text" placeholder="Search..." class="w-full pl-10 pr-4 py-2.5 bg-white/80 border border-gray-200/60 rounded-full text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-100 shadow-sm">
      </div>
    </div>
    <div class="flex items-center gap-5">
      <div class="relative" x-data="{ open: false }" @click.away="open = false">
        <button @click="open = !open" class="flex items-center gap-3 text-left focus:outline-none">
          <div class="text-right">
            <p class="text-sm font-semibold text-gray-800 leading-none">{{ Auth::user()->name ?? 'Seller' }}</p>
            <p class="text-[10px] text-gray-400 mt-0.5 uppercase">SELLER</p>
          </div>
          <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'Seller') }}&background=C0392B&color=fff&size=64" class="w-9 h-9 rounded-xl">
        </button>

        <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg py-1 border border-gray-100 z-50" style="display: none;">
          <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-red-600">Pengaturan Akun</a>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-red-600">Logout</button>
          </form>
        </div>
      </div>
    </div>
  </header>

  {{-- CONTENT --}}
  <main class="flex-1 overflow-y-auto px-8 pb-8">
    @yield('content')
  </main>
</div>

@stack('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
function sellerPanel() {
  return {}
}
</script>
</body>
</html>
