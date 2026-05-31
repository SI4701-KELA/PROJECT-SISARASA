<?php

namespace App\View\Composers;

use App\Models\Cart;
use App\Models\Complaint;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class BuyerSidebarComposer
{
    /**
     * Inject data sidebar buyer ke layout.
     * Menggunakan Cache::remember() dengan TTL 30 detik agar tidak
     * memukul database di setiap request halaman (ini dipanggil per render layout).
     */
    public function compose(View $view): void
    {
        if (!Auth::check()) {
            $view->with([
                'unreadChatCount'       => 0,
                'activeComplaintsCount' => 0,
                'cartCount'             => 0,
            ]);
            return;
        }

        $userId = Auth::id();

        // Cache TTL 30 detik — cukup untuk UX badge, tidak berlebihan
        $unreadChatCount = Cache::remember("buyer_unread_chat_{$userId}", 30, function () use ($userId) {
            return Message::where('receiver_id', $userId)
                ->where('is_read', false)
                ->count();
        });

        $activeComplaintsCount = Cache::remember("buyer_active_complaints_{$userId}", 30, function () use ($userId) {
            return Complaint::where('buyer_id', $userId)
                ->whereIn('status_tiket', ['Open', 'Sedang Diproses'])
                ->count();
        });

        $cartCount = Cache::remember("buyer_cart_count_{$userId}", 30, function () use ($userId) {
            return Cart::where('buyer_id', $userId)->count();
        });

        $view->with([
            'unreadChatCount'       => $unreadChatCount,
            'activeComplaintsCount' => $activeComplaintsCount,
            'cartCount'             => $cartCount,
        ]);
    }
}
