<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use App\Models\Review;
use Illuminate\Http\Request;

class SellerReviewController extends Controller
{
    /**
     * Tampilkan halaman daftar ulasan pelanggan untuk toko seller yang sedang login.
     */
    public function index(Request $request)
    {
        // Cari data seller berdasarkan user_id yang sedang terautentikasi
        $seller = Seller::where('user_id', $request->user()->id)->firstOrFail();

        // Ambil semua ulasan untuk toko ini secara real-time, diurutkan dari yang terbaru
        $reviews = Review::where('seller_id', $seller->id)
            ->with(['buyer', 'order.items.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Hitung rata-rata rating toko (AVR)
        $averageRating = round($reviews->avg('rating') ?? 0.0, 1);
        $totalReviews = $reviews->count();

        // Hitung distribusi jumlah bintang (1 hingga 5) untuk diagram statistik
        $starDistribution = [
            5 => $reviews->where('rating', 5)->count(),
            4 => $reviews->where('rating', 4)->count(),
            3 => $reviews->where('rating', 3)->count(),
            2 => $reviews->where('rating', 2)->count(),
            1 => $reviews->where('rating', 1)->count(),
        ];

        // Hitung persentase untuk masing-masing bintang agar bisa digambar di diagram progres bar
        $starPercentages = [];
        foreach ($starDistribution as $star => $count) {
            $starPercentages[$star] = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0;
        }

        return view('seller.reviews', compact(
            'seller', 'reviews', 'averageRating', 'totalReviews', 'starDistribution', 'starPercentages'
        ));
    }
}
