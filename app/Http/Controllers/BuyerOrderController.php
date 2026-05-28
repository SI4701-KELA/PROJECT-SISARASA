<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class BuyerOrderController extends Controller
{
    /**
     * Menampilkan riwayat pesanan untuk pembeli yang sedang login.
     */
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'riwayat');
        if (!in_array($tab, ['riwayat', 'aktif'])) {
            $tab = 'riwayat';
        }
        
        $query = Order::where('buyer_id', $request->user()->id)
            ->with(['seller', 'items.product', 'review'])
            ->orderBy('created_at', 'desc');

        if ($tab === 'aktif') {
            $orders = $query->whereIn('status', ['menunggu_verifikasi', 'diproses', 'siap_diambil'])->get();
        } else {
            $orders = $query->whereIn('status', ['selesai', 'dibatalkan'])->get();
        }

        // Hitung count per tab untuk badge
        $countAktif = Order::where('buyer_id', $request->user()->id)
            ->whereIn('status', ['menunggu_verifikasi', 'diproses', 'siap_diambil'])
            ->count();
        $countRiwayat = Order::where('buyer_id', $request->user()->id)
            ->whereIn('status', ['selesai', 'dibatalkan'])
            ->count();

        return view('buyer.orders.index', compact(
            'orders', 'tab', 'countAktif', 'countRiwayat'
        ));
    }

    /**
     * Menampilkan detail invoice untuk pesanan tertentu.
     */
    public function show(Request $request, $id)
    {
        $order = Order::where('id', $id)
            ->where('buyer_id', $request->user()->id)
            ->with(['seller.user', 'items.product'])
            ->firstOrFail();

        return view('buyer.orders.show', compact('order'));
    }
}
