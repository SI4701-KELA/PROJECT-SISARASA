<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * PBI-22: Impact Tracker — Dampak Sosial & Lingkungan Platform.
     * Agregasi HANYA dari orders.status = 'Selesai' DAN order_items.is_surplus = true.
     */
    public function impactTracker()
    {
        // Base constraint: hanya order_items surplus dari pesanan Selesai
        // Gunakan 1 JOIN query terpusat untuk semua metrik (menghindari whereHas berulang)
        $surplusBase = \Illuminate\Support\Facades\DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'Selesai')
            ->where('order_items.is_surplus', true);

        // Metrik 1 — Total Food Saved (porsi)
        $totalFoodSaved = (int) (clone $surplusBase)->sum('order_items.qty');

        // Metrik 2 — Nilai Finansial Diselamatkan (Rupiah)
        $financialSaved = (int) (clone $surplusBase)->sum(DB::raw('order_items.qty * order_items.price'));

        // Metrik 3 — Dampak Lingkungan (Asumsi 1 porsi = 0.5 Kg CO2 dicegah)
        $carbonSaved = round($totalFoodSaved * 0.5, 1);

        // Metrik 4 — Total UMKM Kontributor (seller_id unik)
        $totalUmkm = (int) (clone $surplusBase)->distinct()->count('orders.seller_id');

        // Bonus — Top 5 Pahlawan UMKM
        $topSellers = (clone $surplusBase)
            ->join('sellers', 'orders.seller_id', '=', 'sellers.id')
            ->select('sellers.id', 'sellers.store_name', DB::raw('SUM(order_items.qty) as total_porsi'))
            ->groupBy('sellers.id', 'sellers.store_name')
            ->orderByDesc('total_porsi')
            ->limit(5)
            ->get();

        return view('admin.impact-tracker', compact(
            'totalFoodSaved',
            'financialSaved',
            'carbonSaved',
            'totalUmkm',
            'topSellers'
        ));
    }

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
        // Pilih kolom spesifik yang dibutuhkan view — hindari SELECT *
        $sellers = Seller::select([
                'id', 'user_id', 'store_name', 'address', 'verification_status',
                'rejection_reason', 'document_path', 'verified_at',
                'store_photo', 'pending_profile_updates', 'created_at',
            ])
            ->with('user:id,name,email,phone')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.validations', compact('sellers'));
    }

    public function stores()
    {
        // Pilih kolom spesifik yang dibutuhkan view — hindari SELECT *
        $sellers = Seller::select([
                'id', 'user_id', 'store_name', 'address', 'verification_status',
                'rejection_reason', 'document_path', 'verified_at', 'store_photo',
                'open_time', 'close_time', 'latitude', 'longitude',
                'pending_profile_updates', 'created_at',
            ])
            ->with('user:id,name,email,is_banned')
            ->orderBy('created_at', 'desc')
            ->get();
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

    public function reports()
    {
        $reports = \App\Models\Report::with(['buyer', 'seller.user'])
            ->orderBy('seller_id')
            ->latest()
            ->get();
        return view('admin.reports', compact('reports'));
    }

    public function rejectReport($id)
    {
        $report = \App\Models\Report::findOrFail($id);
        
        \App\Models\Report::where('seller_id', $report->seller_id)
            ->where('status', '!=', 'Selesai')
            ->update(['status' => 'Ditolak']);

        return redirect()->route('admin.reports')->with('success', 'Laporan telah ditolak.');
    }

    public function banStore($id)
    {
        $report = \App\Models\Report::with('seller.user')->findOrFail($id);
        
        \App\Models\Report::where('seller_id', $report->seller_id)
            ->update(['status' => 'Selesai']);

        if ($report->seller && $report->seller->user) {
            $user = $report->seller->user;
            $user->is_banned = true;
            $user->save();
        }

        return redirect()->route('admin.reports')->with('success', 'Toko berhasil diblokir secara permanen.');
    }
}
