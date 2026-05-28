<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /**
     * Inbox — Daftar kontak yang pernah chat dengan user ini.
     * Berlaku untuk Buyer maupun Seller.
     */
    public function index()
    {
        $userId = auth()->id();

        // Ambil semua user yang pernah berkirim pesan dengan auth user,
        // urutkan berdasarkan pesan terakhir (terbaru di atas).
        $contacts = DB::table('messages')
            ->select(DB::raw("
                CASE
                    WHEN sender_id = {$userId} THEN receiver_id
                    ELSE sender_id
                END as contact_id
            "))
            ->where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->groupBy('contact_id')
            ->orderByDesc(DB::raw('MAX(created_at)'))
            ->pluck('contact_id');

        // Eager load contact users + pesan terakhir + unread count
        $contactList = $contacts->map(function ($contactId) use ($userId) {
            $contact = User::find($contactId);
            if (!$contact) return null;

            $lastMessage = Message::where(function ($q) use ($userId, $contactId) {
                    $q->where('sender_id', $userId)->where('receiver_id', $contactId);
                })
                ->orWhere(function ($q) use ($userId, $contactId) {
                    $q->where('sender_id', $contactId)->where('receiver_id', $userId);
                })
                ->latest()
                ->first();

            $unreadCount = Message::where('sender_id', $contactId)
                ->where('receiver_id', $userId)
                ->where('is_read', false)
                ->count();

            return (object) [
                'user'         => $contact,
                'lastMessage'  => $lastMessage,
                'unreadCount'  => $unreadCount,
            ];
        })->filter();

        // Tentukan layout berdasarkan role
        $layout = auth()->user()->role === 'seller' ? 'layouts.seller' : 'layouts.buyer';

        return view('chat.index', compact('contactList', 'layout'));
    }

    /**
     * Ruang Chat — Tampilkan percakapan dengan kontak tertentu.
     */
    public function show(User $contact)
    {
        $userId = auth()->id();

        // PRIVACY: pastikan user pernah chat dengan contact ini,
        // ATAU izinkan memulai chat baru (buyer → seller, seller → buyer)
        $this->authorizeChat($userId, $contact->id);

        // Tandai semua pesan masuk dari contact sebagai Read
        Message::where('sender_id', $contact->id)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $layout = auth()->user()->role === 'seller' ? 'layouts.seller' : 'layouts.buyer';

        return view('chat.show', compact('contact', 'layout'));
    }

    /**
     * API Endpoint: GET — Ambil riwayat pesan (untuk AJAX polling).
     * Urut kronologis (terlama di atas).
     */
    public function fetchMessages(User $contact)
    {
        $userId = auth()->id();

        $this->authorizeChat($userId, $contact->id);

        $messages = Message::where(function ($q) use ($userId, $contact) {
                $q->where('sender_id', $userId)->where('receiver_id', $contact->id);
            })
            ->orWhere(function ($q) use ($userId, $contact) {
                $q->where('sender_id', $contact->id)->where('receiver_id', $userId);
            })
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) use ($userId) {
                return [
                    'id'         => $msg->id,
                    'sender_id'  => $msg->sender_id,
                    'message'    => e($msg->message),
                    'is_mine'    => $msg->sender_id === $userId,
                    'time'       => $msg->created_at->format('H:i'),
                    'date'       => $msg->created_at->format('d M Y'),
                    'is_read'    => $msg->is_read,
                ];
            });

        // Tandai pesan masuk sebagai read saat di-fetch
        Message::where('sender_id', $contact->id)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    /**
     * API Endpoint: POST — Kirim pesan baru (CR only, tanpa Update/Delete).
     */
    public function sendMessage(Request $request, User $contact)
    {
        $userId = auth()->id();

        $this->authorizeChat($userId, $contact->id);

        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $message = Message::create([
            'sender_id'   => $userId,
            'receiver_id' => $contact->id,
            'message'     => $request->message,
        ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'         => $message->id,
                'sender_id'  => $message->sender_id,
                'message'    => e($message->message),
                'is_mine'    => true,
                'time'       => $message->created_at->format('H:i'),
                'date'       => $message->created_at->format('d M Y'),
                'is_read'    => false,
            ],
        ]);
    }

    /**
     * Privacy guard: validasi bahwa user berhak mengakses percakapan ini.
     * User harus pernah chat ATAU salah satu dari mereka harus seller/buyer.
     */
    private function authorizeChat(int $userId, int $contactId): void
    {
        // Cegah chat dengan diri sendiri
        if ($userId === $contactId) {
            abort(403, 'Tidak dapat melakukan chat dengan diri sendiri.');
        }

        // Cek apakah contact ada
        $contact = User::find($contactId);
        if (!$contact) {
            abort(404, 'User tidak ditemukan.');
        }

        // Izinkan jika sudah pernah chat
        $hasHistory = Message::where(function ($q) use ($userId, $contactId) {
                $q->where('sender_id', $userId)->where('receiver_id', $contactId);
            })
            ->orWhere(function ($q) use ($userId, $contactId) {
                $q->where('sender_id', $contactId)->where('receiver_id', $userId);
            })
            ->exists();

        if ($hasHistory) return;

        // Izinkan memulai chat baru jika buyer↔seller
        $authUser = User::find($userId);
        $validPair = ($authUser->role === 'buyer' && $contact->role === 'seller')
                  || ($authUser->role === 'seller' && $contact->role === 'buyer');

        if (!$validPair) {
            abort(403, 'Anda tidak memiliki izin untuk chat dengan user ini.');
        }
    }
}
