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

        // Ambil daftar contact_id + last message time dalam 1 query
        $contactIds = DB::table('messages')
            ->selectRaw("
                CASE
                    WHEN sender_id = {$userId} THEN receiver_id
                    ELSE sender_id
                END as contact_id,
                MAX(created_at) as last_at
            ")
            ->where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->groupBy('contact_id')
            ->orderByDesc('last_at')
            ->pluck('contact_id');

        if ($contactIds->isEmpty()) {
            $layout = auth()->user()->role === 'seller' ? 'layouts.seller' : 'layouts.buyer';
            return view('chat.index', ['contactList' => collect(), 'layout' => $layout]);
        }

        // Eager load semua users sekaligus (1 query, bukan N query)
        $users = User::whereIn('id', $contactIds)->get()->keyBy('id');

        // Ambil lastMessage per kontak dalam 1 query (self-join / subquery)
        $lastMessages = DB::table('messages as m1')
            ->whereNotExists(function ($q) {
                $q->from('messages as m2')
                    ->whereRaw('m2.created_at > m1.created_at')
                    ->whereRaw('(
                        (m2.sender_id = m1.sender_id AND m2.receiver_id = m1.receiver_id) OR
                        (m2.sender_id = m1.receiver_id AND m2.receiver_id = m1.sender_id)
                    )');
            })
            ->where(function ($q) use ($userId) {
                $q->where('m1.sender_id', $userId)
                  ->orWhere('m1.receiver_id', $userId);
            })
            ->select('m1.*')
            ->get()
            ->groupBy(function ($msg) use ($userId) {
                return $msg->sender_id === $userId ? $msg->receiver_id : $msg->sender_id;
            })
            ->map(function ($group) {
                $msg = $group->first();
                if ($msg && isset($msg->created_at)) {
                    $msg->created_at = \Carbon\Carbon::parse($msg->created_at);
                }
                return $msg;
            });

        // Ambil unread counts per kontak dalam 1 query (GROUP BY)
        $unreadCounts = DB::table('messages')
            ->selectRaw('sender_id, COUNT(*) as cnt')
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->groupBy('sender_id')
            ->pluck('cnt', 'sender_id');

        // Rakit contactList dari data yang sudah tersedia — tanpa query tambahan
        $contactList = $contactIds->map(function ($contactId) use ($users, $lastMessages, $unreadCounts) {
            $contact = $users->get($contactId);
            if (!$contact) return null;

            return (object) [
                'user'        => $contact,
                'lastMessage' => $lastMessages->get($contactId),
                'unreadCount' => $unreadCounts->get($contactId, 0),
            ];
        })->filter()->values();

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
