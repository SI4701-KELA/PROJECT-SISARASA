<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FavoriteStore;
use App\Models\Seller;

class FavoriteController extends Controller
{
    /**
     * Toggle favorit toko: INSERT jika belum ada, DELETE jika sudah ada.
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'seller_id' => 'required|exists:sellers,id',
        ]);

        $userId = auth()->id();
        $sellerId = $request->input('seller_id');

        // Cek apakah sudah difavoritkan
        $existing = FavoriteStore::where('user_id', $userId)
            ->where('seller_id', $sellerId)
            ->first();

        if ($existing) {
            // Detach: Hapus dari favorit
            $existing->delete();
            return back()->with('success', 'Toko berhasil dihapus dari favorit.');
        }

        // Attach: Tambahkan ke favorit
        FavoriteStore::create([
            'user_id' => $userId,
            'seller_id' => $sellerId,
        ]);

        return back()->with('success', 'Toko berhasil ditambahkan ke favorit!');
    }

    /**
     * Tampilkan halaman Toko Tersimpan milik user yang sedang login.
     */
    public function index()
    {
        $userId = auth()->id();

        // Tarik semua seller yang difavoritkan beserta waktu favorit
        $favoriteRecords = FavoriteStore::where('user_id', $userId)
            ->with(['seller' => function ($query) {
                // withSum langsung di query — tidak perlu load seluruh products hanya untuk sum
                $query->withSum('stocks as total_surplus_sum', 'qty_surplus');
            }])
            ->latest()
            ->get();

        // Rakit seller collection dari records
        $sellers = $favoriteRecords->map(function ($fav) {
            $seller = $fav->seller;
            if ($seller) {
                $seller->total_surplus  = (int) ($seller->total_surplus_sum ?? 0);
                $seller->favorited_at   = $fav->created_at;
            }
            return $seller;
        })->filter();

        // Ambil daftar favorit sebagai array untuk tombol hati
        $userFavorites = FavoriteStore::where('user_id', $userId)
            ->pluck('seller_id')
            ->toArray();

        return view('buyer.favorites.index', compact('sellers', 'userFavorites'));
    }
}
