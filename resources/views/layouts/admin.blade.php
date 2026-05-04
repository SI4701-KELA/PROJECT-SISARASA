<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Admin Console') - Sisa Rasa</title>
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
<body class="overflow-hidden h-screen" x-data="adminPanel()">
<div class="flex h-screen">

{{-- SIDEBAR --}}
<aside class="w-60 bg-white flex flex-col border-r border-gray-100 flex-shrink-0">
  <div class="px-7 pt-8 pb-5">
    <p class="text-xl font-black tr tracking-tight">Sisa Rasa</p>
    <p class="text-[9px] font-semibold text-gray-400 tracking-[0.18em] uppercase mt-0.5">Admin Console</p>
  </div>
  <nav class="flex-1 px-3 space-y-0.5 mt-2">
    {{-- Stores --}}
    <a href="{{ route('admin.stores') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-sm relative {{ request()->routeIs('admin.stores') ? 'tr bg-red-50/40' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50' }}">
      <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
      Stores
      @if(request()->routeIs('admin.stores'))
        <div class="sidebar-bar"></div>
      @endif
    </a>
    {{-- Validation Queue --}}
    <a href="{{ route('admin.validations') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm relative {{ request()->routeIs('admin.validations') ? 'tr font-semibold bg-red-50/40' : 'text-gray-400 font-medium hover:text-gray-600 hover:bg-gray-50' }}">
      <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
      Validation Queue
      @if(request()->routeIs('admin.validations'))
        <div class="sidebar-bar"></div>
      @endif
    </a>
    {{-- Reports --}}
    <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 font-medium text-sm hover:text-gray-600 hover:bg-gray-50">
      <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>Reports
    </a>
    {{-- Settings --}}
    <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 font-medium text-sm hover:text-gray-600 hover:bg-gray-50">
      <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>Settings
    </a>
  </nav>

  <div class="px-3 pb-8 space-y-0.5">
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
      <div class="relative" x-data="{ open: false }" @click.away="open = false">
        <button @click="open = !open" class="flex items-center gap-3 text-left focus:outline-none">
          <div class="text-right">
            <p class="text-sm font-semibold text-gray-800 leading-none">{{ Auth::user()->name ?? 'Admin' }}</p>
            <p class="text-[10px] text-gray-400 mt-0.5">Super Admin</p>
          </div>
          <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'Admin') }}&background=C0392B&color=fff&size=64" class="w-9 h-9 rounded-xl">
        </button>

        <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg py-1 border border-gray-100 z-50" style="display: none;">
          <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-red-600">Profile</a>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-red-600">Logout</button>
          </form>
        </div>
      </div>
    </div>
  </header>

  {{-- CONTENT --}}
  <main class="flex-1 overflow-hidden px-8 pb-8">
    @yield('content')
  </main>
</div>

@stack('scripts')
</body>
</html>
