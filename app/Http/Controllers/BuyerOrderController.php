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
    /**
     * Membatalkan pesanan oleh pembeli.
     */
    public function cancel(Request $request, $id)
    {
        $request->validate([
            'cancellation_reason' => 'required|string',
        ], [
            'cancellation_reason.required' => 'Alasan pembatalan wajib diisi.',
        ]);

        $order = Order::where('id', $id)
            ->where('buyer_id', $request->user()->id)
            ->with('items')
            ->firstOrFail();

        // Security Guard: Tolak request jika status bukan 'menunggu_verifikasi' ATAU waktu 15 detik sudah habis
        if ($order->status !== 'menunggu_verifikasi' || now()->diffInSeconds($order->created_at) > 15) {
            abort(400, 'Pesanan tidak dapat dibatalkan.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($order, $request) {
            $order->update([
                'status' => 'dibatalkan',
                'cancellation_reason' => $request->input('cancellation_reason'),
            ]);

            // Kembalikan stok
            foreach ($order->items as $item) {
                $stock = \App\Models\Stock::where('product_id', $item->product_id)->first();
                if ($stock) {
                    if ($item->is_surplus) {
                        $stock->increment('qty_surplus', $item->qty);
                    } else {
                        $stock->increment('qty_reg', $item->qty);
                    }
                }
            }
        });

        return redirect()->back()->with('success', 'Pesanan berhasil dibatalkan.');
    }
}
