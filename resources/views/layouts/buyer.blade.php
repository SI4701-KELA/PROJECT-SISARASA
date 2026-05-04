<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Buyer Console') - Sisa Rasa</title>
@vite(['resources/css/app.css', 'resources/js/app.js'])
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*{font-family:'Inter',sans-serif;}body{background:#F7F5F3;}
.sidebar-bar{position:absolute;left:-1px;top:50%;transform:translateY(-50%);width:4px;height:32px;background:#C0392B;border-radius:0 4px 4px 0;}
.tr{color:#C0392B;}.bg-tr{background:#C0392B;}
::-webkit-scrollbar{width:4px;}::-webkit-scrollbar-thumb{background:#E0DADA;border-radius:10px;}
</style>
@stack('styles')
</head>
<body class="overflow-hidden h-screen" x-data="buyerPanel()">
<div class="flex h-screen">

{{-- SIDEBAR --}}
<aside class="w-64 bg-white flex flex-col border-r border-gray-100 flex-shrink-0 relative">
  <div class="px-7 pt-8 pb-8">
    <p class="text-2xl font-black tr tracking-tight">Sisa Rasa</p>
    <p class="text-[10px] font-bold text-gray-400 tracking-[0.2em] uppercase mt-1">Buyer Console</p>
  </div>
  <nav class="flex-1 px-4 space-y-2 mt-2 overflow-y-auto">
    {{-- Daftar Menu --}}
    <a href="{{ route('buyer.menu') }}" class="flex items-center gap-4 px-4 py-3.5 rounded-2xl text-sm relative transition-all {{ request()->routeIs('buyer.menu') ? 'tr font-bold bg-red-50/50 shadow-sm' : 'text-gray-500 font-semibold hover:text-gray-700 hover:bg-gray-50' }}">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
      Daftar Menu
      @if(request()->routeIs('buyer.menu'))
        <div class="sidebar-bar -left-4"></div>
      @endif
    </a>
    
    {{-- Daftar Toko --}}
    <a href="{{ route('buyer.stores') }}" class="flex items-center gap-4 px-4 py-3.5 rounded-2xl text-sm relative transition-all {{ request()->routeIs('buyer.stores') ? 'tr font-bold bg-red-50/50 shadow-sm' : 'text-gray-500 font-semibold hover:text-gray-700 hover:bg-gray-50' }}">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
      Daftar Toko
      @if(request()->routeIs('buyer.stores'))
        <div class="sidebar-bar -left-4"></div>
      @endif
    </a>

    {{-- Toko Terdekat --}}
    <a href="{{ route('buyer.nearby') }}" class="flex items-center gap-4 px-4 py-3.5 rounded-2xl text-sm relative transition-all {{ request()->routeIs('buyer.nearby') ? 'tr font-bold bg-red-50/50 shadow-sm' : 'text-gray-500 font-semibold hover:text-gray-700 hover:bg-gray-50' }}">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
      Toko Terdekat
      @if(request()->routeIs('buyer.nearby'))
        <div class="sidebar-bar -left-4"></div>
      @endif
    </a>

    {{-- Order --}}
    <a href="#" class="flex items-center gap-4 px-4 py-3.5 rounded-2xl text-gray-500 font-semibold text-sm hover:text-gray-700 hover:bg-gray-50 transition-all">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>Order
    </a>

    {{-- Toko Favorit --}}
    <a href="{{ route('buyer.favorites.index') }}" class="flex items-center gap-4 px-4 py-3.5 rounded-2xl text-sm relative transition-all {{ request()->routeIs('buyer.favorites.index') ? 'tr font-bold bg-red-50/50 shadow-sm' : 'text-gray-500 font-semibold hover:text-gray-700 hover:bg-gray-50' }}">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
      Toko Favorit
      @if(request()->routeIs('buyer.favorites.index'))
        <div class="sidebar-bar -left-4"></div>
      @endif
    </a>

    {{-- Rating & Ulasan --}}
    <a href="#" class="flex items-center gap-4 px-4 py-3.5 rounded-2xl text-gray-500 font-semibold text-sm hover:text-gray-700 hover:bg-gray-50 transition-all">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>Rating & Ulasan
    </a>

    {{-- Riwayat Pesanan --}}
    <a href="#" class="flex items-center gap-4 px-4 py-3.5 rounded-2xl text-gray-500 font-semibold text-sm hover:text-gray-700 hover:bg-gray-50 transition-all">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Riwayat Pesanan
    </a>
  </nav>

  <div class="px-4 pb-8 mt-4">
    <form method="POST" action="{{ route('logout') }}">@csrf
      <button type="submit" class="w-full flex items-center gap-4 px-4 py-3.5 rounded-2xl text-gray-500 font-semibold text-sm hover:text-red-600 hover:bg-red-50 transition-all">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>Logout
      </button>
    </form>
  </div>
</aside>

{{-- MAIN --}}
<div class="flex-1 flex flex-col overflow-hidden">
  {{-- TOPBAR --}}
  <header class="px-10 py-5 flex items-center justify-between gap-6 border-b border-gray-100/50 bg-white/50 backdrop-blur-md">
    <div class="flex-1 max-w-xl">
      <div class="relative">
        <svg class="absolute left-5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="text" placeholder="Search..." class="w-full pl-12 pr-4 py-3 bg-white border border-gray-100 rounded-full text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-100 shadow-sm transition-all font-medium">
      </div>
    </div>
    
    <div class="flex items-center gap-8">
      {{-- Cart Icon --}}
      <button class="relative text-gray-400 hover:text-terracotta transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        <span class="absolute -top-1.5 -right-2 bg-red-500 text-white text-[10px] font-bold w-4 h-4 flex items-center justify-center rounded-full border-2 border-white">0</span>
      </button>

      {{-- Profile Dropdown --}}
      <div class="relative" x-data="{ open: false }" @click.away="open = false">
        <button @click="open = !open" class="flex items-center gap-3 text-left focus:outline-none group">
          <div class="w-10 h-10 rounded-full bg-terracotta flex items-center justify-center text-white font-bold shadow-sm group-hover:scale-105 transition-transform">
            {{ strtoupper(substr(Auth::user()->name ?? 'B', 0, 1)) }}
          </div>
          <div class="hidden md:block">
            <p class="text-sm font-bold text-gray-800 leading-none group-hover:text-terracotta transition-colors">{{ Auth::user()->name ?? 'Buyer' }}</p>
            <p class="text-[9px] font-bold text-gray-400 mt-1 uppercase tracking-widest">BUYER</p>
          </div>
          <svg class="w-4 h-4 text-gray-400 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>

        <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 mt-3 w-48 bg-white rounded-2xl shadow-xl py-2 border border-gray-100 z-50" style="display: none;">
          <a href="{{ route('profile.edit') }}" class="block px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:text-terracotta transition-colors">Pengaturan Akun</a>
          <div class="h-px bg-gray-100 my-1"></div>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="block w-full text-left px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors">Logout</button>
          </form>
        </div>
      </div>
    </div>
  </header>

  {{-- CONTENT --}}
  <main class="flex-1 overflow-y-auto px-10 py-8">
    @yield('content')
  </main>
</div>

@stack('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
function buyerPanel() {
  return {}
}
</script>
</body>
</html>
