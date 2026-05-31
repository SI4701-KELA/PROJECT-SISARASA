<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Seller;
use Illuminate\Http\Request;

class SellerOrderController extends Controller
{
    /**
     * Tampilkan daftar pesanan milik toko seller yang sedang login.
     */
    public function index(Request $request)
    {
        $seller = Seller::where('user_id', $request->user()->id)->firstOrFail();

        $tab = $request->input('tab', 'baru');

        // Query pesanan berdasarkan tab
        $query = Order::where('seller_id', $seller->id)
            ->with(['buyer', 'items.product'])
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

        // Hitung count per tab dalam 1 query GROUP BY status
        $statusCounts = Order::where('seller_id', $seller->id)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $countBaru     = (int) $statusCounts->get('menunggu_verifikasi', 0);
        $countDiproses = (int) $statusCounts->get('diproses', 0);
        $countSiap     = (int) $statusCounts->get('siap_diambil', 0);
        $countSelesai  = (int) ($statusCounts->get('selesai', 0) + $statusCounts->get('dibatalkan', 0));

        return view('seller.orders', compact(
            'orders', 'tab', 'seller',
            'countBaru', 'countDiproses', 'countSiap', 'countSelesai'
        ));
    }

    /**
     * Terima pembayaran → Status: menunggu_verifikasi → diproses.
     */
    public function acceptPayment(Request $request, $id)
    {
        $seller = Seller::where('user_id', $request->user()->id)->firstOrFail();

        $order = Order::where('id', $id)
            ->where('seller_id', $seller->id)
            ->where('status', 'menunggu_verifikasi')
            ->firstOrFail();

        $order->update(['status' => 'diproses']);

        return redirect()->route('seller.orders', ['tab' => 'diproses'])
            ->with('success', 'Pembayaran diterima. Pesanan #' . $order->id . ' sedang diproses.');
    }

    /**
     * Tolak pembayaran → Status: menunggu_verifikasi → dibatalkan.
     * Stok dikembalikan via DB::transaction.
     */
    public function rejectPayment(Request $request, $id)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|min:5',
        ], [
            'cancellation_reason.required' => 'Alasan penolakan wajib diisi.',
            'cancellation_reason.min'      => 'Alasan penolakan minimal 5 karakter.',
        ]);

        $seller = Seller::where('user_id', $request->user()->id)->firstOrFail();

        $order = Order::where('id', $id)
            ->where('seller_id', $seller->id)
            ->where('status', 'menunggu_verifikasi')
            ->with('items')
            ->firstOrFail();

        \Illuminate\Support\Facades\DB::transaction(function () use ($order, $request) {
            $order->update([
                'status'              => 'dibatalkan',
                'cancellation_reason' => $request->input('cancellation_reason'),
            ]);

            // Kembalikan stok — identik dengan BuyerOrderController::cancel
            foreach ($order->items as $item) {
                $stock = \App\Models\Stock::where('product_id', $item->product_id)
                    ->lockForUpdate()
                    ->first();

                if ($stock) {
                    if ($item->is_surplus) {
                        $stock->increment('qty_surplus', $item->qty);
                    } else {
                        $stock->increment('qty_reg', $item->qty);
                    }
                }
            }
        });

        return redirect()->route('seller.orders', ['tab' => 'baru'])
            ->with('success', 'Pembayaran ditolak. Pesanan #' . $order->id . ' telah dibatalkan dan stok dikembalikan.');
    }

    /**
     * Tandai makanan siap → Status: diproses → siap_diambil.
     */
    public function markReady(Request $request, $id)
    {
        $seller = Seller::where('user_id', $request->user()->id)->firstOrFail();

        $order = Order::where('id', $id)
            ->where('seller_id', $seller->id)
            ->where('status', 'diproses')
            ->firstOrFail();

        $now = now();
        $pickupDeadline = $now->copy()->addHours(2);
        
        if ($seller->close_time) {
            $closeTime = now()->setTimeFromTimeString($seller->close_time);
            
            if ($closeTime->lessThan($now)) {
                $closeTime->addDay();
            }

            if ($pickupDeadline->greaterThan($closeTime)) {
                $pickupDeadline = $closeTime;
            }
        }

        $order->update([
            'status' => 'siap_diambil',
            'pickup_deadline' => $pickupDeadline
        ]);

        return redirect()->route('seller.orders', ['tab' => 'siap'])
            ->with('success', 'Pesanan #' . $order->id . ' siap diambil oleh pembeli.');
    }

    /**
     * Verifikasi pengambilan makanan menggunakan kode unik / QR Code.
     */
    public function verifyOrder(Request $request)
    {
        $request->validate([
            'pickup_code' => 'required|string',
        ]);

        $seller = Seller::where('user_id', $request->user()->id)->firstOrFail();
        $pickupCode = strtoupper(trim($request->input('pickup_code')));

        // Jika penjual memasukkan 5 digit akhir saja, tambahkan prefix "SISA-"
        if (strlen($pickupCode) === 5) {
            $pickupCode = 'SISA-' . $pickupCode;
        }

        // Cari pesanan dengan pickup_code dan seller_id yang cocok, serta berstatus siap_diambil
        $order = Order::where('seller_id', $seller->id)
            ->where('pickup_code', $pickupCode)
            ->where('status', 'siap_diambil')
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Kode tidak valid!'
            ], 422);
        }

        $order->update([
            'status' => 'selesai'
        ]);

        session()->flash('success', 'Pesanan Berhasil Diserahkan. Pesanan #' . $order->id . ' telah selesai.');

        return response()->json([
            'success' => true,
            'message' => 'Pesanan Berhasil Diserahkan'
        ]);
    }
    /**
     * Membatalkan pesanan oleh penjual.
     */
    public function cancel(Request $request, $id)
    {
        $request->validate([
            'cancellation_reason' => 'required|string',
        ], [
            'cancellation_reason.required' => 'Alasan pembatalan wajib diisi.',
        ]);

        $seller = Seller::where('user_id', $request->user()->id)->firstOrFail();

        $order = Order::where('id', $id)
            ->where('seller_id', $seller->id)
            ->with('items')
            ->firstOrFail();

        // Penjual dapat membatalkan pesanan selama status BUKAN "siap_diambil", "selesai", atau "dibatalkan"
        if (in_array($order->status, ['siap_diambil', 'selesai', 'dibatalkan'])) {
            return redirect()->back()->with('error', 'Pesanan tidak dapat dibatalkan pada status ini.');
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

        // Redirect ke tab yang sesuai atau back
        return redirect()->back()->with('success', 'Pesanan #' . $order->id . ' telah dibatalkan.');
    }
}
