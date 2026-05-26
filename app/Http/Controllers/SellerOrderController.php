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

        // Hitung count per tab untuk badge
        $countBaru = Order::where('seller_id', $seller->id)->where('status', 'menunggu_verifikasi')->count();
        $countDiproses = Order::where('seller_id', $seller->id)->where('status', 'diproses')->count();
        $countSiap = Order::where('seller_id', $seller->id)->where('status', 'siap_diambil')->count();
        $countSelesai = Order::where('seller_id', $seller->id)->whereIn('status', ['selesai', 'dibatalkan'])->count();

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
     */
    public function rejectPayment(Request $request, $id)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|min:5',
        ], [
            'cancellation_reason.required' => 'Alasan penolakan wajib diisi.',
            'cancellation_reason.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        $seller = Seller::where('user_id', $request->user()->id)->firstOrFail();

        $order = Order::where('id', $id)
            ->where('seller_id', $seller->id)
            ->where('status', 'menunggu_verifikasi')
            ->firstOrFail();

        $order->update([
            'status' => 'dibatalkan',
            'cancellation_reason' => $request->input('cancellation_reason'),
        ]);

        return redirect()->route('seller.orders', ['tab' => 'baru'])
            ->with('success', 'Pembayaran ditolak. Pesanan #' . $order->id . ' telah dibatalkan.');
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
}
