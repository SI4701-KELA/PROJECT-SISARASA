<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Menyimpan ulasan dari pembeli untuk pesanan tertentu.
     */
    public function store(Request $request)
    {
        // Validasi wajib isi rating bintang (1-5), komentar bersifat opsional (nullable)
        $request->validate([
            'order_id' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ], [
            'rating.required' => 'Kamu wajib memilih jumlah Bintang (1 hingga 5) untuk ulasan!',
            'rating.integer' => 'Format rating harus berupa angka bulat.',
            'rating.min' => 'Rating bintang minimal adalah 1.',
            'rating.max' => 'Rating bintang maksimal adalah 5.',
        ]);

        // Pastikan pesanan benar-benar ada dan milik pembeli yang login
        $order = Order::where('id', $request->order_id)
            ->where('buyer_id', $request->user()->id)
            ->firstOrFail();

        // Syarat Status Pesanan: Pembeli HANYA bisa menekan tombol ulasan jika status transaksinya "Selesai"
        if ($order->status !== 'selesai') {
            return redirect()->back()->with('error', 'Kamu hanya bisa memberikan ulasan pada pesanan yang sudah selesai!');
        }

        // Anti-Spam Review: Sistem Backend harus menolak insert data ganda pada ID Pesanan yang sama
        $existingReview = Review::where('order_id', $order->id)->exists();
        if ($existingReview) {
            return redirect()->back()->with('error', 'Kamu tidak bisa mengirim ulasan lebih dari sekali untuk pesanan yang sama!');
        }

        // Simpan data ulasan baru ke tabel reviews
        Review::create([
            'order_id' => $order->id,
            'buyer_id' => $order->buyer_id,
            'seller_id' => $order->seller_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect()->back()->with('success', 'Terima kasih banyak atas penilaian bintang dan ulasan yang kamu berikan! Feedback-mu sangat berharga bagi UMKM.');
    }
}
