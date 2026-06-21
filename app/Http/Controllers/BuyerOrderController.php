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
        
        $buyerId = $request->user()->id;

        $query = Order::where('buyer_id', $buyerId)
            ->with(['seller', 'items.product', 'review'])
            ->orderBy('created_at', 'desc');

        if ($tab === 'aktif') {
            $orders = $query->whereIn('status', ['menunggu_verifikasi', 'diproses', 'siap_diambil'])->get();
        } else {
            $orders = $query->whereIn('status', ['selesai', 'dibatalkan'])->get();
        }

        // Hitung count per tab dalam 1 query GROUP BY status
        $statusCounts = Order::where('buyer_id', $buyerId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $countAktif = (int) (
            $statusCounts->get('menunggu_verifikasi', 0) +
            $statusCounts->get('diproses', 0) +
            $statusCounts->get('siap_diambil', 0)
        );
        $countRiwayat = (int) (
            $statusCounts->get('selesai', 0) +
            $statusCounts->get('dibatalkan', 0)
        );

        return response()->view('buyer.orders.index', compact(
            'orders', 'tab', 'countAktif', 'countRiwayat'
        ))->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
          ->header('Pragma', 'no-cache')
          ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
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

        return response()->view('buyer.orders.show', compact('order'))
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
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

        $elapsedSeconds = now()->getTimestamp() - $order->created_at->getTimestamp();
        // Security Guard: Tolak request jika status bukan 'menunggu_verifikasi'/'diproses' ATAU waktu 15 detik sudah habis
        if (!in_array($order->status, ['menunggu_verifikasi', 'diproses']) || $elapsedSeconds > 15) {
            abort(400, 'Pesanan tidak dapat dibatalkan. Batas waktu telah habis.');
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
