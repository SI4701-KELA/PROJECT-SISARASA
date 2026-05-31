@extends('layouts.seller')

@section('title', 'Analitik Penjualan')

@section('content')
<div class="max-w-6xl mx-auto">

    {{-- Header & Filter --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Analitik Penjualan</h1>
            <p class="text-gray-500 font-medium mt-1">Pantau performa toko dan dampak penyelamatan makananmu.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @php
                $filters = [
                    'today' => 'Hari Ini',
                    'week'  => 'Minggu Ini',
                    'month' => 'Bulan Ini',
                    'all'   => 'Semua Waktu',
                ];
            @endphp
            @foreach($filters as $key => $label)
                <a href="{{ route('seller.analytics', ['filter' => $key]) }}"
                   id="filter-{{ $key }}"
                   class="px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider transition-all duration-200
                          {{ $filter === $key
                              ? 'bg-[#C0392B] text-white shadow-lg shadow-red-200/50 scale-105'
                              : 'bg-white text-gray-500 border border-gray-200 hover:border-[#C0392B]/40 hover:text-[#C0392B] hover:shadow-sm' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Metric Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

        {{-- Card 1: Total Pendapatan --}}
        <div id="card-pendapatan" class="relative bg-white rounded-3xl border border-gray-100/80 shadow-sm p-6 lg:p-8 overflow-hidden group hover:shadow-lg transition-all duration-300">
            <div class="card-bar"></div>
            {{-- Background Glow --}}
            <div class="absolute -top-8 -right-8 w-32 h-32 bg-emerald-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative">
                <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2">Total Pendapatan</p>
                <p class="text-3xl font-black text-gray-900 tracking-tight">
                    Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
                </p>
                <p class="text-xs font-semibold text-gray-400 mt-2">{{ $totalTransaksi }} transaksi selesai</p>
            </div>
        </div>

        {{-- Card 2: Makanan Diselamatkan (Surplus) --}}
        <div id="card-surplus" class="relative bg-white rounded-3xl border border-gray-100/80 shadow-sm p-6 lg:p-8 overflow-hidden group hover:shadow-lg transition-all duration-300">
            <div class="card-bar" style="background: #c04b36 !important;"></div>
            <div class="absolute -top-8 -right-8 w-32 h-32 bg-orange-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative">
                <div class="w-12 h-12 bg-orange-50 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-[#c04b36]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2">Makanan Diselamatkan</p>
                <p class="text-3xl font-black text-gray-900 tracking-tight">
                    {{ number_format($porsiSurplus, 0, ',', '.') }} <span class="text-lg font-bold text-gray-400">Porsi</span>
                </p>
                <p class="text-xs font-semibold text-[#c04b36] mt-2 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"/></svg>
                    Dampak Penyelamatan Sisa Rasa
                </p>
            </div>
        </div>

        {{-- Card 3: Porsi Reguler Terjual --}}
        <div id="card-reguler" class="relative bg-white rounded-3xl border border-gray-100/80 shadow-sm p-6 lg:p-8 overflow-hidden group hover:shadow-lg transition-all duration-300">
            <div class="card-bar" style="background: #2aab7f !important;"></div>
            <div class="absolute -top-8 -right-8 w-32 h-32 bg-teal-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative">
                <div class="w-12 h-12 bg-teal-50 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-[#2aab7f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                    </svg>
                </div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2">Porsi Reguler Terjual</p>
                <p class="text-3xl font-black text-gray-900 tracking-tight">
                    {{ number_format($porsiReguler, 0, ',', '.') }} <span class="text-lg font-bold text-gray-400">Porsi</span>
                </p>
                <p class="text-xs font-semibold text-gray-400 mt-2">Penjualan harga normal</p>
            </div>
        </div>

    </div>

    {{-- Chart & Summary Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

        {{-- Doughnut Chart --}}
        <div class="lg:col-span-3 bg-white rounded-3xl border border-gray-100/80 shadow-sm p-6 lg:p-8">
            <h3 class="text-base font-black text-gray-900 mb-1 uppercase tracking-wider">Komposisi Penjualan</h3>
            <p class="text-xs text-gray-400 font-medium mb-6">Perbandingan porsi surplus vs. reguler</p>

            @if($totalPorsi === 0)
                {{-- Empty State --}}
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-5">
                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-gray-500">Belum ada data penjualan</p>
                    <p class="text-xs text-gray-400 mt-1">Grafik akan muncul setelah ada pesanan selesai.</p>
                </div>
            @else
                <div class="relative flex justify-center">
                    <canvas id="salesChart" width="320" height="320"></canvas>
                </div>
            @endif
        </div>

        {{-- Summary Side Panel --}}
        <div class="lg:col-span-2 bg-white rounded-3xl border border-gray-100/80 shadow-sm p-6 lg:p-8 flex flex-col">
            <h3 class="text-base font-black text-gray-900 mb-1 uppercase tracking-wider">Ringkasan</h3>
            <p class="text-xs text-gray-400 font-medium mb-6">Detail rincian penjualanmu</p>

            <div class="space-y-4 flex-1">
                {{-- Surplus Row --}}
                <div class="flex items-center justify-between p-4 bg-orange-50/60 border border-orange-100/60 rounded-2xl">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 rounded-full bg-[#c04b36] shrink-0"></div>
                        <span class="text-sm font-bold text-gray-700">Surplus (Sisa Rasa)</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-black text-gray-900">{{ number_format($porsiSurplus, 0, ',', '.') }}</p>
                        <p class="text-[10px] font-bold text-gray-400 uppercase">Porsi</p>
                    </div>
                </div>

                {{-- Reguler Row --}}
                <div class="flex items-center justify-between p-4 bg-emerald-50/60 border border-emerald-100/60 rounded-2xl">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 rounded-full bg-[#2aab7f] shrink-0"></div>
                        <span class="text-sm font-bold text-gray-700">Reguler (Harga Normal)</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-black text-gray-900">{{ number_format($porsiReguler, 0, ',', '.') }}</p>
                        <p class="text-[10px] font-bold text-gray-400 uppercase">Porsi</p>
                    </div>
                </div>

                {{-- Total Row --}}
                <div class="flex items-center justify-between p-4 bg-gray-50 border border-gray-100/60 rounded-2xl mt-auto">
                    <span class="text-sm font-black text-gray-700 uppercase tracking-wide">Total Porsi</span>
                    <div class="text-right">
                        <p class="text-lg font-black text-gray-900">{{ number_format($totalPorsi, 0, ',', '.') }}</p>
                        <p class="text-[10px] font-bold text-gray-400 uppercase">Porsi Terjual</p>
                    </div>
                </div>
            </div>

            {{-- Percentage Bars --}}
            @if($totalPorsi > 0)
                <div class="mt-6 pt-5 border-t border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.15em] mb-3">Distribusi Persentase</p>
                    @php
                        $pctSurplus = round(($porsiSurplus / $totalPorsi) * 100, 1);
                        $pctReguler = round(($porsiReguler / $totalPorsi) * 100, 1);
                    @endphp
                    <div class="space-y-3">
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="font-bold text-[#c04b36]">Surplus</span>
                                <span class="font-black text-gray-700">{{ $pctSurplus }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                <div class="h-2.5 rounded-full bg-gradient-to-r from-[#c04b36] to-[#e06050] transition-all duration-700" style="width: {{ $pctSurplus }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="font-bold text-[#2aab7f]">Reguler</span>
                                <span class="font-black text-gray-700">{{ $pctReguler }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                <div class="h-2.5 rounded-full bg-gradient-to-r from-[#2aab7f] to-[#3dd69e] transition-all duration-700" style="width: {{ $pctReguler }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('salesChart');
    if (!canvas) return;

    const porsiReguler = {{ $porsiReguler ?? 0 }};
    const porsiSurplus = {{ $porsiSurplus ?? 0 }};

    // Guard: jangan render chart jika total 0
    if (porsiReguler === 0 && porsiSurplus === 0) return;

    const ctx = canvas.getContext('2d');

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Reguler', 'Surplus (Sisa Rasa)'],
            datasets: [{
                data: [porsiReguler, porsiSurplus],
                backgroundColor: ['#2aab7f', '#c04b36'],
                hoverBackgroundColor: ['#23926c', '#a8402e'],
                borderWidth: 0,
                borderRadius: 6,
                spacing: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 24,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        font: {
                            family: 'Inter',
                            size: 12,
                            weight: '700'
                        },
                        color: '#6B7280'
                    }
                },
                tooltip: {
                    backgroundColor: '#1F2937',
                    titleFont: { family: 'Inter', weight: '800', size: 13 },
                    bodyFont:  { family: 'Inter', weight: '600', size: 12 },
                    padding: 14,
                    cornerRadius: 12,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            const total = porsiReguler + porsiSurplus;
                            const pct = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                            return ' ' + context.label + ': ' + context.raw.toLocaleString('id-ID') + ' porsi (' + pct + '%)';
                        }
                    }
                }
            },
            animation: {
                animateRotate: true,
                animateScale: true,
                duration: 1200,
                easing: 'easeOutQuart'
            }
        }
    });
});
</script>
@endpush
