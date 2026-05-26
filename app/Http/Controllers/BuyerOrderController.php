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
        $tab = $request->input('tab', 'semua');
        
        $query = Order::where('buyer_id', $request->user()->id)
            ->with(['seller', 'items.product'])
            ->orderBy('created_at', 'desc');

        if ($tab === 'baru') {
            $orders = $query->where('status', 'menunggu_verifikasi')->get();
        } elseif ($tab === 'diproses') {
            $orders = $query->where('status', 'diproses')->get();
        } elseif ($tab === 'siap') {
            $orders = $query->where('status', 'siap_diambil')->get();
        } elseif ($tab === 'selesai') {
            $orders = $query->whereIn('status', ['selesai', 'dibatalkan'])->get();
        } else {
            $orders = $query->get();
        }

        // Hitung count per tab untuk badge
        $countSemua = Order::where('buyer_id', $request->user()->id)->count();
        $countBaru = Order::where('buyer_id', $request->user()->id)->where('status', 'menunggu_verifikasi')->count();
        $countDiproses = Order::where('buyer_id', $request->user()->id)->where('status', 'diproses')->count();
        $countSiap = Order::where('buyer_id', $request->user()->id)->where('status', 'siap_diambil')->count();
        $countSelesai = Order::where('buyer_id', $request->user()->id)->whereIn('status', ['selesai', 'dibatalkan'])->count();

        return view('buyer.orders.index', compact(
            'orders', 'tab', 
            'countSemua', 'countBaru', 'countDiproses', 'countSiap', 'countSelesai'
        ));
    }
}
