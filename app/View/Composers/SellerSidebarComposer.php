<?php

namespace App\View\Composers;

use App\Models\Complaint;
use App\Models\Message;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SellerSidebarComposer
{
    /**
     * Inject data sidebar seller ke layout.
     * Menggunakan Cache::remember() dengan TTL 30 detik agar tidak
     * memukul database di setiap request halaman.
     */
    public function compose(View $view): void
    {
        if (!Auth::check()) {
            $view->with([
                'pendingOrdersCount'        => 0,
                'unreadSellerChatCount'     => 0,
                'incomingComplaintsCount'   => 0,
                'sellerForOrders'           => null,
                'sellerModel'               => null,
            ]);
            return;
        }

        $userId = Auth::id();

        // Ambil seller model — satu kali, di-cache
        $sellerForOrders = Cache::remember("seller_model_{$userId}", 60, function () {
            return Auth::user()->seller ?? null;
        });

        $pendingOrdersCount = 0;
        $incomingComplaintsCount = 0;

        if ($sellerForOrders) {
            $sellerId = $sellerForOrders->id;

            $pendingOrdersCount = Cache::remember("seller_pending_orders_{$userId}", 30, function () use ($sellerId) {
                return Order::where('seller_id', $sellerId)
                    ->where('status', 'menunggu_verifikasi')
                    ->count();
            });

            $incomingComplaintsCount = Cache::remember("seller_incoming_complaints_{$userId}", 30, function () use ($sellerId) {
                return Complaint::where('seller_id', $sellerId)
                    ->whereIn('status_tiket', ['Open', 'Sedang Diproses'])
                    ->count();
            });
        }

        $unreadSellerChatCount = Cache::remember("seller_unread_chat_{$userId}", 30, function () use ($userId) {
            return Message::where('receiver_id', $userId)
                ->where('is_read', false)
                ->count();
        });

        $view->with([
            'pendingOrdersCount'      => $pendingOrdersCount,
            'unreadSellerChatCount'   => $unreadSellerChatCount,
            'incomingComplaintsCount' => $incomingComplaintsCount,
            'sellerForOrders'         => $sellerForOrders,
            'sellerModel'             => $sellerForOrders,
        ]);
    }
}
