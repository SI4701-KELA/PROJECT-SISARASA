<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function viewDocument($id)
    {
        $seller = Seller::findOrFail($id);

        if (!$seller->document_path || !Storage::exists($seller->document_path)) {
            abort(404, 'Dokumen tidak ditemukan.');
        }

        return Storage::download($seller->document_path);
    }

    public function validations()
    {
        $sellers = Seller::orderBy('created_at', 'desc')->get();
        return view('admin.validations', compact('sellers'));
    }

    public function stores()
    {
        $sellers = Seller::orderBy('created_at', 'desc')->get();
        return view('admin.stores', compact('sellers'));
    }

    public function verifySeller(Request $request, $id)
    {
        $seller = Seller::findOrFail($id);
        
        $status_action = $request->input('status_action');
        
        if (in_array($status_action, ['rejected', 'suspended'])) {
            $request->validate([
                'rejection_reason' => 'required|string'
            ]);
            $seller->rejection_reason = $request->input('rejection_reason');
            $seller->verified_at = null;
        } elseif ($status_action === 'approved') {
            $seller->rejection_reason = null;
            $seller->verified_at = now();
        }

        $seller->verification_status = $status_action;
        $seller->save();

        return redirect()->route('admin.stores')->with('success', 'Status penjual berhasil diperbarui.');
    }

    /**
     * APPROVE pending profile update: timpa data asli dengan data antrean, lalu kosongkan antrean.
     */
    public function approveUpdate($id)
    {
        $seller = Seller::findOrFail($id);

        if (!$seller->pending_profile_updates) {
            return redirect()->route('admin.stores')->with('error', 'Tidak ada perubahan data yang perlu disetujui.');
        }

        $pending = $seller->pending_profile_updates;

        // Timpa kolom-kolom utama dengan data dari antrean
        $seller->store_name    = $pending['store_name']    ?? $seller->store_name;
        $seller->address       = $pending['address']       ?? $seller->address;
        $seller->latitude      = $pending['latitude']      ?? $seller->latitude;
        $seller->longitude     = $pending['longitude']     ?? $seller->longitude;
        $seller->open_time     = $pending['open_time']     ?? $seller->open_time;
        $seller->discount_time = $pending['discount_time'] ?? $seller->discount_time;
        $seller->close_time    = $pending['close_time']    ?? $seller->close_time;

        // Bersihkan antrean setelah disetujui
        $seller->pending_profile_updates = null;
        $seller->save();

        return redirect()->route('admin.stores')->with('success', '✅ Perubahan profil toko "' . $seller->store_name . '" telah disetujui dan diterapkan.');
    }

    /**
     * REJECT pending profile update: data asli tetap utuh, antrean dimusnahkan.
     */
    public function rejectUpdate($id)
    {
        $seller = Seller::findOrFail($id);

        // Musnahkan antrean, biarkan data asli tetap sedia kala
        $seller->pending_profile_updates = null;
        $seller->save();

        return redirect()->route('admin.stores')->with('success', '❌ Usulan perubahan profil toko "' . $seller->store_name . '" telah ditolak.');
    }
}
