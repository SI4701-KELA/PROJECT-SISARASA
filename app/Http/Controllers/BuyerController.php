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
                $q->where('verification_status', 'approved');
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
            $lat = $request->lat;
            $lng = $request->lng;
            
            // Rumus Haversine untuk kalkulasi jarak (dalam KM)
            $haversineRaw = '( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance';
            
            $sellers = Seller::select('*')
                ->where('verification_status', 'approved')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->selectRaw($haversineRaw, [$lat, $lng, $lat])
                ->orderBy('distance', 'asc')
                ->get();
        } else {
            // Jika lokasi belum dideteksi atau model belum siap
            $sellers = class_exists(Seller::class) ? Seller::all() : collect([]);
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
        $sellers = Seller::where('verification_status', 'approved')->withCount('products')->get();

        // Injeksi array favorit buyer untuk tombol hati
        $userFavorites = auth()->check()
            ? FavoriteStore::where('user_id', auth()->id())->pluck('seller_id')->toArray()
            : [];
        
        return view('buyer.stores', compact('sellers', 'userFavorites'));
    }
}
