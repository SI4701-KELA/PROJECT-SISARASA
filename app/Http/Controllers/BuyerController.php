<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seller;
use App\Models\FavoriteStore;

class BuyerController extends Controller
{
    public function menu(Request $request)
    {
        $categories = \App\Models\Category::all();
        $categoryId = $request->input('category_id');

        $query = \App\Models\Product::with(['seller', 'category', 'stock', 'discounts'])
            ->whereHas('seller', function ($q) {
                $q->where('verification_status', 'approved')
                  ->whereHas('user', function ($uq) {
                      $uq->where('is_banned', false);
                  });
            });

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->get();

        return view('buyer.menu', compact('categories', 'categoryId', 'products'));
    }

    /**
     * Menampilkan halaman toko terdekat (nearby).
     */
    public function nearby(Request $request)
    {
        $hasLocation = $request->has('lat') && $request->has('lng');
        
        if ($hasLocation && class_exists(Seller::class)) {
            $lat = (float) $request->lat;
            $lng = (float) $request->lng;
            
<<<<<<< Updated upstream
            // Rumus Haversine untuk kalkulasi jarak (dalam KM)
            $haversineRaw = '( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance';
            
            $sellers = Seller::select('*')
                ->where('verification_status', 'approved')
=======
            // Ambil semua seller yang approved, tidak banned, dan punya koordinat
            $sellers = Seller::where('verification_status', 'approved')
>>>>>>> Stashed changes
                ->whereHas('user', function ($q) {
                    $q->where('is_banned', false);
                })
=======
            // Ambil semua seller yang approved dan punya koordinat
            $sellers = Seller::where('verification_status', 'approved')
>>>>>>> Stashed changes
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get()
                ->map(function ($seller) use ($lat, $lng) {
                    // Rumus Haversine untuk kalkulasi jarak (dalam KM) — dihitung di PHP agar kompatibel SQLite
                    $earthRadius = 6371;
                    $dLat = deg2rad($seller->latitude - $lat);
                    $dLng = deg2rad($seller->longitude - $lng);
                    $a = sin($dLat / 2) * sin($dLat / 2)
                       + cos(deg2rad($lat)) * cos(deg2rad($seller->latitude))
                       * sin($dLng / 2) * sin($dLng / 2);
                    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
                    $seller->distance = round($earthRadius * $c, 2);
                    return $seller;
                })
                ->sortBy('distance')
                ->values();
        } else {
            // Jika lokasi belum dideteksi atau model belum siap
            $sellers = class_exists(Seller::class) ? Seller::whereHas('user', function($q) { $q->where('is_banned', false); })->get() : collect([]);
        }

        // Injeksi array favorit buyer untuk tombol hati
        $userFavorites = auth()->check()
            ? FavoriteStore::where('user_id', auth()->id())->pluck('seller_id')->toArray()
            : [];

        // Kirimkan data ke view
        return view('buyer.nearby', compact('sellers', 'hasLocation', 'userFavorites'));
    }

    /**
     * Menampilkan daftar semua toko/UMKM.
     */
    public function stores()
    {
        $sellers = Seller::where('verification_status', 'approved')
            ->whereHas('user', function ($q) {
                $q->where('is_banned', false);
            })
            ->withCount('products')->get();

        // Injeksi array favorit buyer untuk tombol hati
        $userFavorites = auth()->check()
            ? FavoriteStore::where('user_id', auth()->id())->pluck('seller_id')->toArray()
            : [];
        
        return view('buyer.stores', compact('sellers', 'userFavorites'));
    }
    /**
     * Menampilkan halaman detail toko: profil toko + semua produknya.
     */
    public function storeDetail($id)
    {
        $seller = Seller::where('verification_status', 'approved')
            ->whereHas('user', function ($q) {
                $q->where('is_banned', false);
            })
            ->with(['products.discounts'])
            ->findOrFail($id);

        return view('buyer.store-show', compact('seller'));
    }
}
