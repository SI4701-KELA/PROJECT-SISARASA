<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Seller;

class LandingController extends Controller
{
    public function index()
    {
        // Hitung metrik dampak lingkungan
        $surplusBase = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'Selesai')
            ->where('order_items.is_surplus', true);

        // Metrik 1 — Total Food Saved (porsi)
        $totalFoodSaved = (int) (clone $surplusBase)->sum('order_items.qty');

        // Metrik 2 — Dampak Lingkungan (Asumsi 1 porsi = 0.5 Kg CO2 dicegah)
        $carbonSaved = round($totalFoodSaved * 0.5, 1);

        // Metrik 3 — Total UMKM Tergabung (Semua seller yang sudah diverifikasi)
        $totalUmkm = Seller::where('verification_status', 'approved')->count();

        // Top 3 Pahlawan UMKM
        $topSellers = (clone $surplusBase)
            ->join('sellers', 'orders.seller_id', '=', 'sellers.id')
            ->select('sellers.id', 'sellers.store_name', 'sellers.store_photo', 'sellers.address', DB::raw('SUM(order_items.qty) as total_porsi'))
            ->groupBy('sellers.id', 'sellers.store_name', 'sellers.store_photo', 'sellers.address')
            ->orderByDesc('total_porsi')
            ->limit(3)
            ->get();

        return view('landing', compact(
            'totalFoodSaved',
            'carbonSaved',
            'totalUmkm',
            'topSellers'
        ));
    }
}
