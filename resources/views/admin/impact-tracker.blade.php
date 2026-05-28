@extends('layouts.admin')

@section('title', 'Impact Tracker')

@push('styles')
<style>
    .impact-hero {
        background: linear-gradient(135deg, #065f46 0%, #047857 30%, #059669 60%, #10b981 100%);
        position: relative;
        overflow: hidden;
    }
    .impact-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(255,255,255,0.06) 0%, transparent 70%);
        border-radius: 50%;
    }
    .impact-hero::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -10%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255,255,255,0.04) 0%, transparent 70%);
        border-radius: 50%;
    }
    .counter-up {
        animation: counterFadeIn 0.8s ease-out forwards;
    }
    @keyframes counterFadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .card-float { animation: cardFloat 3s ease-in-out infinite; }
    @keyframes cardFloat {
        0%, 100% { transform: translateY(0); }
        50%      { transform: translateY(-4px); }
    }
    .rank-badge {
        width: 32px; height: 32px;
        display: flex; align-items: center; justify-content: center;
        font-weight: 900; font-size: 12px; border-radius: 12px;
    }
</style>
@endpush

@section('content')
<div class="h-full overflow-y-auto -mx-8 -mb-8 px-8 pb-8">

    {{-- ══════════════════════════════════════════════════════════════
         HERO SECTION — Tipografi Raksasa
         ══════════════════════════════════════════════════════════════ --}}
    <div class="impact-hero rounded-3xl p-8 lg:p-12 mb-8 relative">
        <div class="relative z-10">
            {{-- Label --}}
            <div class="flex items-center gap-2 mb-6">
                <div class="w-8 h-8 bg-white/15 rounded-xl flex items-center justify-center backdrop-blur-sm">
                    <svg class="w-4 h-4 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-emerald-200 text-[10px] font-black uppercase tracking-[0.25em]">Sisa Rasa Impact Tracker</span>
            </div>

            {{-- Big Number --}}
            <div class="counter-up">
                <p id="hero-food-saved" class="text-7xl lg:text-8xl font-black text-white tracking-tighter leading-none">
                    {{ number_format($totalFoodSaved, 0, ',', '.') }}
                </p>
                <p class="text-xl lg:text-2xl font-bold text-emerald-200 mt-3 tracking-tight">
                    Porsi Makanan Berhasil Diselamatkan
                </p>
                <p class="text-sm text-emerald-300/70 font-medium mt-2 max-w-lg">
                    Setiap porsi yang diselamatkan adalah satu langkah menuju Indonesia tanpa pemborosan pangan. Terima kasih, para pahlawan UMKM! 🌿
                </p>
            </div>

            {{-- Decorative floating particles --}}
            <div class="absolute top-6 right-8 w-3 h-3 bg-emerald-300/20 rounded-full card-float"></div>
            <div class="absolute top-20 right-24 w-2 h-2 bg-emerald-300/15 rounded-full card-float" style="animation-delay:0.5s"></div>
            <div class="absolute bottom-8 right-16 w-4 h-4 bg-emerald-300/10 rounded-full card-float" style="animation-delay:1s"></div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         GRID 3 KARTU PENDUKUNG
         ══════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

        {{-- Kartu 1: Nilai Ekonomi --}}
        <div id="card-financial" class="relative bg-white rounded-3xl border border-gray-100/80 shadow-sm p-6 lg:p-8 overflow-hidden group hover:shadow-lg transition-all duration-300">
            <div class="card-bar"></div>
            <div class="absolute -top-8 -right-8 w-32 h-32 bg-amber-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative">
                <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2">Nilai Ekonomi</p>
                <p class="text-2xl lg:text-3xl font-black text-gray-900 tracking-tight">
                    Rp {{ number_format($financialSaved, 0, ',', '.') }}
                </p>
                <p class="text-xs font-semibold text-amber-600 mt-2 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/></svg>
                    Kerugian Finansial Dicegah
                </p>
            </div>
        </div>

        {{-- Kartu 2: Dampak Emisi CO₂ --}}
        <div id="card-carbon" class="relative bg-white rounded-3xl border border-gray-100/80 shadow-sm p-6 lg:p-8 overflow-hidden group hover:shadow-lg transition-all duration-300">
            <div class="card-bar" style="background: #059669 !important;"></div>
            <div class="absolute -top-8 -right-8 w-32 h-32 bg-emerald-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative">
                <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>
                    </svg>
                </div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2">Dampak Emisi</p>
                <p class="text-2xl lg:text-3xl font-black text-gray-900 tracking-tight">
                    {{ number_format($carbonSaved, 1, ',', '.') }} <span class="text-lg font-bold text-gray-400">Kg</span>
                </p>
                <p class="text-xs font-semibold text-emerald-600 mt-2 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/></svg>
                    Emisi CO₂ Berhasil Dicegah
                </p>
            </div>
        </div>

        {{-- Kartu 3: Pahlawan UMKM --}}
        <div id="card-umkm" class="relative bg-white rounded-3xl border border-gray-100/80 shadow-sm p-6 lg:p-8 overflow-hidden group hover:shadow-lg transition-all duration-300">
            <div class="card-bar" style="background: #c04b36 !important;"></div>
            <div class="absolute -top-8 -right-8 w-32 h-32 bg-red-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative">
                <div class="w-12 h-12 bg-red-50 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-[#c04b36]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2">Pahlawan UMKM</p>
                <p class="text-2xl lg:text-3xl font-black text-gray-900 tracking-tight">
                    {{ number_format($totalUmkm, 0, ',', '.') }} <span class="text-lg font-bold text-gray-400">Mitra</span>
                </p>
                <p class="text-xs font-semibold text-[#c04b36] mt-2 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"/></svg>
                    UMKM Berkontribusi Aktif
                </p>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════════
         LEADERBOARD — Top 5 Pahlawan UMKM
         ══════════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-3xl border border-gray-100/80 shadow-sm overflow-hidden">
        <div class="p-6 lg:p-8 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-50 rounded-2xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744l.311 1.242.443.905.905.443 1.242.311a1 1 0 010 1.934l-1.242.311-.905.443-.443.905-.311 1.242a1 1 0 01-1.934 0l-.311-1.242-.443-.905-.905-.443-1.242-.311a1 1 0 010-1.934l1.242-.311.905-.443.443-.905.311-1.242A1 1 0 0112 2z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-black text-gray-900 tracking-tight">Top 5 Pahlawan Penyelamat Makanan</h3>
                    <p class="text-xs text-gray-400 font-medium">UMKM dengan kontribusi surplus terbanyak</p>
                </div>
            </div>
        </div>

        @if($topSellers->isEmpty())
            {{-- Empty State --}}
            <div class="p-12 text-center">
                <div class="w-20 h-20 mx-auto bg-gray-50 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <p class="text-sm font-bold text-gray-500">Belum ada data kontributor</p>
                <p class="text-xs text-gray-400 mt-1">Leaderboard akan muncul setelah ada pesanan surplus yang selesai.</p>
            </div>
        @else
            <div class="divide-y divide-gray-50">
                @foreach($topSellers as $index => $seller)
                    @php
                        $rankColors = [
                            0 => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'border' => 'border-amber-200'],
                            1 => ['bg' => 'bg-gray-100',  'text' => 'text-gray-600',  'border' => 'border-gray-200'],
                            2 => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'border' => 'border-orange-200'],
                        ];
                        $rc = $rankColors[$index] ?? ['bg' => 'bg-gray-50', 'text' => 'text-gray-500', 'border' => 'border-gray-100'];
                        $rankIcons = ['🥇', '🥈', '🥉'];
                    @endphp
                    <div class="flex items-center gap-5 px-6 lg:px-8 py-5 hover:bg-gray-50/50 transition-colors group">
                        {{-- Rank Badge --}}
                        <div class="rank-badge {{ $rc['bg'] }} {{ $rc['text'] }} border {{ $rc['border'] }}">
                            @if($index < 3)
                                <span class="text-base">{{ $rankIcons[$index] }}</span>
                            @else
                                {{ $index + 1 }}
                            @endif
                        </div>

                        {{-- Avatar --}}
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 font-black text-sm shrink-0 group-hover:scale-105 transition-transform">
                            {{ strtoupper(substr($seller->store_name ?? 'T', 0, 1)) }}
                        </div>

                        {{-- Store Info --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-900 truncate">{{ $seller->store_name ?? 'Toko Tidak Dikenal' }}</p>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Mitra UMKM Sisa Rasa</p>
                        </div>

                        {{-- Porsi Count --}}
                        <div class="text-right shrink-0">
                            <p class="text-lg font-black text-gray-900">{{ number_format($seller->total_porsi, 0, ',', '.') }}</p>
                            <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider">Porsi Diselamatkan</p>
                        </div>

                        {{-- Progress visual hint --}}
                        @if($index === 0 && $seller->total_porsi > 0)
                            <div class="hidden lg:block w-20">
                                <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-1.5 rounded-full bg-gradient-to-r from-amber-400 to-amber-500" style="width: 100%"></div>
                                </div>
                            </div>
                        @elseif($topSellers->first()->total_porsi > 0)
                            <div class="hidden lg:block w-20">
                                <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-1.5 rounded-full bg-gradient-to-r from-emerald-300 to-emerald-400" style="width: {{ round(($seller->total_porsi / $topSellers->first()->total_porsi) * 100) }}%"></div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
