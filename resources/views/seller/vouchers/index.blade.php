@extends('layouts.seller')

@section('title', 'Kelola Voucher')

@section('content')
<div class="max-w-7xl" x-data="{ addOpen: false }">
    {{-- Success and Error Alerts --}}
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl shadow-sm font-medium text-sm flex items-center gap-3">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl shadow-sm font-medium text-sm">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl shadow-sm font-medium text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="flex justify-between items-start mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight mb-2">Kelola Voucher Toko</h1>
            <p class="text-sm text-gray-500 font-medium">Buat dan atur kode promo untuk menarik minat pembeli belanja di toko Anda.</p>
        </div>
        <button @click="addOpen = true" class="bg-terracotta hover:bg-[#a6402d] text-white font-bold py-2.5 px-6 rounded-full shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-terracotta text-sm flex items-center gap-2">
            <span>+</span> Tambah Voucher
        </button>
    </div>

    {{-- Voucher List --}}
    @if($vouchers->isEmpty())
        <div class="text-center py-20 bg-white rounded-[24px] border border-gray-100 shadow-sm">
            <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4 text-terracotta">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">Belum Ada Voucher</h3>
            <p class="text-gray-500 text-sm max-w-sm mx-auto font-medium mb-6">Mulai buat voucher pertama Anda untuk meningkatkan transaksi penjualan warung Anda.</p>
            <button @click="addOpen = true" class="inline-flex items-center bg-terracotta hover:bg-[#a6402d] text-white font-bold py-2 px-5 rounded-full text-xs transition-all shadow-sm">
                Tambah Voucher Sekarang
            </button>
        </div>
    @else
        <div class="bg-white rounded-[24px] border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/75 border-b border-gray-100 text-xs font-black text-gray-400 uppercase tracking-widest">
                            <th class="px-6 py-4">Kode Voucher</th>
                            <th class="px-6 py-4">Tipe & Potongan</th>
                            <th class="px-6 py-4">Min. Belanja</th>
                            <th class="px-6 py-4">Masa Berlaku</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-sm text-gray-700 font-medium">
                        @foreach($vouchers as $voucher)
                            <tr x-data="{ editOpen: false }">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono bg-gray-50 border border-gray-100 rounded-lg px-2.5 py-1.5 text-xs font-bold text-gray-800 tracking-wider">
                                        {{ $voucher->code }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($voucher->type === 'percent')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-bold">
                                            🎉 Potongan {{ $voucher->value }}%
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-50 text-green-700 rounded-full text-xs font-bold">
                                            💰 Potongan Rp {{ number_format($voucher->value, 0, ',', '.') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900 font-bold">
                                    Rp {{ number_format($voucher->min_order, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 font-semibold">
                                    @if($voucher->expires_at)
                                        @if($voucher->expires_at->isPast())
                                            <span class="text-red-500 font-bold">Kedaluwarsa ({{ $voucher->expires_at->format('d M Y H:i') }})</span>
                                        @else
                                            Berlaku s/d {{ $voucher->expires_at->format('d M Y H:i') }}
                                        @endif
                                    @else
                                        <span class="text-gray-400 italic">Selamanya (Tidak Ada Batas)</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <form action="{{ route('seller.vouchers.toggle-status', $voucher->id) }}" method="POST">
                                        @csrf @method('PATCH')
                                        @if($voucher->is_active)
                                            <button type="submit" class="inline-flex items-center px-3 py-1 bg-emerald-50 hover:bg-emerald-100 border border-emerald-100 rounded-full text-xs font-bold text-emerald-700 transition-colors shadow-sm">
                                                ● Aktif
                                            </button>
                                        @else
                                            <button type="submit" class="inline-flex items-center px-3 py-1 bg-gray-100 hover:bg-gray-200 border border-gray-200 rounded-full text-xs font-bold text-gray-500 transition-colors">
                                                ○ Non-Aktif
                                            </button>
                                        @endif
                                    </form>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-xs font-bold">
                                    <div class="flex items-center justify-center gap-3">
                                        <button @click="editOpen = true" class="text-blue-600 hover:text-blue-800 transition-colors">
                                            Edit
                                        </button>
                                        <form action="{{ route('seller.vouchers.destroy', $voucher->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus voucher ini?');" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 transition-colors">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>

                                {{-- Edit Voucher Modal --}}
                                <div x-show="editOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                                        <div x-show="editOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" @click="editOpen = false"></div>

                                        <div x-show="editOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full relative z-50">
                                            <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center">
                                                <h3 class="text-lg font-bold text-gray-900">Edit Voucher</h3>
                                                <button @click="editOpen = false" class="text-gray-400 hover:text-gray-600">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                            <form action="{{ route('seller.vouchers.update', $voucher->id) }}" method="POST" class="p-6 space-y-4">
                                                @csrf @method('PUT')
                                                
                                                <div>
                                                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1.5">Kode Voucher <span class="text-red-500">*</span></label>
                                                    <input type="text" name="code" value="{{ $voucher->code }}" required placeholder="Contoh: PROMOHEMAT" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-terracotta focus:ring-1 focus:ring-terracotta font-mono font-bold tracking-wider">
                                                </div>

                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1.5">Tipe Potongan <span class="text-red-500">*</span></label>
                                                        <select name="type" required class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-terracotta focus:ring-1 focus:ring-terracotta font-semibold">
                                                            <option value="fixed" {{ $voucher->type === 'fixed' ? 'selected' : '' }}>Potongan Tetap (Rupiah)</option>
                                                            <option value="percent" {{ $voucher->type === 'percent' ? 'selected' : '' }}>Persentase (%)</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1.5">Nilai Potongan <span class="text-red-500">*</span></label>
                                                        <input type="number" name="value" value="{{ $voucher->value }}" min="1" required class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-terracotta focus:ring-1 focus:ring-terracotta font-semibold">
                                                    </div>
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1.5">Min. Belanja (Rupiah) <span class="text-red-500">*</span></label>
                                                    <input type="number" name="min_order" value="{{ $voucher->min_order }}" min="0" required class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-terracotta focus:ring-1 focus:ring-terracotta font-semibold">
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1.5">Tanggal Kedaluwarsa <span class="text-gray-400 font-medium text-[10px]">(Opsional)</span></label>
                                                    <input type="datetime-local" name="expires_at" value="{{ $voucher->expires_at ? $voucher->expires_at->format('Y-m-d\TH:i') : '' }}" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-terracotta focus:ring-1 focus:ring-terracotta font-semibold">
                                                </div>

                                                <label class="flex items-center gap-2 pt-2 cursor-pointer">
                                                    <input type="checkbox" name="is_active" value="1" {{ $voucher->is_active ? 'checked' : '' }} class="w-4 h-4 text-terracotta border-gray-300 focus:ring-terracotta rounded">
                                                    <span class="text-xs font-bold text-gray-700">Aktifkan Voucher Ini</span>
                                                </label>

                                                <div class="pt-4 flex justify-end gap-2 border-t border-gray-100">
                                                    <button type="button" @click="editOpen = false" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition-all">Batal</button>
                                                    <button type="submit" class="px-5 py-2.5 bg-terracotta hover:bg-[#a6402d] text-white font-bold text-xs rounded-xl transition-all shadow-md">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Add Voucher Modal --}}
    <div x-show="addOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div x-show="addOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" @click="addOpen = false"></div>

            <div x-show="addOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full relative z-50">
                <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900">Tambah Voucher Baru</h3>
                    <button @click="addOpen = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form action="{{ route('seller.vouchers.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1.5">Kode Voucher <span class="text-red-500">*</span></label>
                        <input type="text" name="code" required placeholder="Contoh: PROMOHEMAT" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-terracotta focus:ring-1 focus:ring-terracotta font-mono font-bold tracking-wider">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1.5">Tipe Potongan <span class="text-red-500">*</span></label>
                            <select name="type" required class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-terracotta focus:ring-1 focus:ring-terracotta font-semibold">
                                <option value="fixed" selected>Potongan Tetap (Rupiah)</option>
                                <option value="percent">Persentase (%)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1.5">Nilai Potongan <span class="text-red-500">*</span></label>
                            <input type="number" name="value" min="1" required placeholder="10000 atau 10" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-terracotta focus:ring-1 focus:ring-terracotta font-semibold">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1.5">Min. Belanja (Rupiah) <span class="text-red-500">*</span></label>
                        <input type="number" name="min_order" value="0" min="0" required class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-terracotta focus:ring-1 focus:ring-terracotta font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1.5">Tanggal Kedaluwarsa <span class="text-gray-400 font-medium text-[10px]">(Opsional)</span></label>
                        <input type="datetime-local" name="expires_at" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-terracotta focus:ring-1 focus:ring-terracotta font-semibold">
                    </div>

                    <label class="flex items-center gap-2 pt-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 text-terracotta border-gray-300 focus:ring-terracotta rounded">
                        <span class="text-xs font-bold text-gray-700">Aktifkan Voucher Ini</span>
                    </label>

                    <div class="pt-4 flex justify-end gap-2 border-t border-gray-100">
                        <button type="button" @click="addOpen = false" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition-all">Batal</button>
                        <button type="submit" class="px-5 py-2.5 bg-terracotta hover:bg-[#a6402d] text-white font-bold text-xs rounded-xl transition-all shadow-md">Tambah Voucher</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
