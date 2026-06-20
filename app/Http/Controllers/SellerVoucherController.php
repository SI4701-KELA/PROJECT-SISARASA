<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use App\Models\Voucher;
use Illuminate\Http\Request;

class SellerVoucherController extends Controller
{
    /**
     * Tampilkan daftar voucher milik seller.
     */
    public function index(Request $request)
    {
        $seller = Seller::where('user_id', $request->user()->id)->first();

        if (!$seller) {
            return redirect()->route('seller.profile')
                ->with('error', 'Silakan lengkapi profil toko terlebih dahulu.');
        }

        $vouchers = Voucher::where('seller_id', $seller->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('seller.vouchers.index', compact('vouchers'));
    }

    /**
     * Simpan voucher baru.
     */
    public function store(Request $request)
    {
        $seller = Seller::where('user_id', $request->user()->id)->first();

        if (!$seller) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        // Sanitasi kode ke UPPERCASE sebelum divalidasi
        if ($request->has('code')) {
            $request->merge(['code' => strtoupper(trim($request->code))]);
        }

        $rules = [
            'code' => 'required|string|unique:vouchers,code|max:50',
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:1',
            'min_order' => 'required|numeric|min:0',
            'expires_at' => 'nullable|date|after:today',
        ];

        // Jika persentase, nilai tidak boleh lebih dari 100
        if ($request->input('type') === 'percent') {
            $rules['value'] .= '|max:100';
        }

        $request->validate($rules, [
            'code.unique' => 'Kode voucher sudah digunakan oleh toko lain.',
            'value.max' => 'Nilai persentase potongan tidak boleh melebihi 100%.',
            'expires_at.after' => 'Tanggal kedaluwarsa harus setelah hari ini.',
        ]);

        Voucher::create([
            'seller_id' => $seller->id,
            'code' => $request->code,
            'type' => $request->type,
            'value' => $request->value,
            'min_order' => $request->min_order,
            'is_active' => $request->has('is_active'),
            'expires_at' => $request->expires_at,
        ]);

        return redirect()->back()->with('success', 'Voucher berhasil ditambahkan.');
    }

    /**
     * Perbarui voucher yang ada.
     */
    public function update(Request $request, $id)
    {
        $seller = Seller::where('user_id', $request->user()->id)->first();
        if (!$seller) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $voucher = Voucher::where('seller_id', $seller->id)->findOrFail($id);

        // Sanitasi kode ke UPPERCASE sebelum divalidasi
        if ($request->has('code')) {
            $request->merge(['code' => strtoupper(trim($request->code))]);
        }

        $rules = [
            'code' => 'required|string|unique:vouchers,code,' . $voucher->id . '|max:50',
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:1',
            'min_order' => 'required|numeric|min:0',
            'expires_at' => 'nullable|date',
        ];

        if ($request->input('type') === 'percent') {
            $rules['value'] .= '|max:100';
        }

        $request->validate($rules, [
            'code.unique' => 'Kode voucher sudah digunakan oleh toko lain.',
            'value.max' => 'Nilai persentase potongan tidak boleh melebihi 100%.',
        ]);

        $voucher->update([
            'code' => $request->code,
            'type' => $request->type,
            'value' => $request->value,
            'min_order' => $request->min_order,
            'is_active' => $request->has('is_active'),
            'expires_at' => $request->expires_at,
        ]);

        return redirect()->back()->with('success', 'Voucher berhasil diperbarui.');
    }

    /**
     * Hapus voucher.
     */
    public function destroy(Request $request, $id)
    {
        $seller = Seller::where('user_id', $request->user()->id)->first();
        if (!$seller) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $voucher = Voucher::where('seller_id', $seller->id)->findOrFail($id);
        $voucher->delete();

        return redirect()->back()->with('success', 'Voucher berhasil dihapus.');
    }

    /**
     * Toggle status keaktifan voucher.
     */
    public function toggleStatus(Request $request, $id)
    {
        $seller = Seller::where('user_id', $request->user()->id)->first();
        if (!$seller) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $voucher = Voucher::where('seller_id', $seller->id)->findOrFail($id);
        $voucher->is_active = !$voucher->is_active;
        $voucher->save();

        $statusText = $voucher->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Voucher berhasil {$statusText}.");
    }
}
