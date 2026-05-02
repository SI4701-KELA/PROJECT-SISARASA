<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seller;

class BuyerController extends Controller
{
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
                ->where('status_verified', 'approved')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->selectRaw($haversineRaw, [$lat, $lng, $lat])
                ->orderBy('distance', 'asc')
                ->get();
        } else {
            // Jika lokasi belum dideteksi atau model belum siap
            $sellers = class_exists(Seller::class) ? Seller::all() : collect([]);
        }

        // Kirimkan data ke view
        return view('buyer.nearby', compact('sellers', 'hasLocation'));
    }

    /**
     * Menampilkan daftar semua toko/UMKM.
     */
    public function stores()
    {
        $sellers = Seller::where('status_verified', 'approved')->withCount('products')->get();
        
        return view('buyer.stores', compact('sellers'));
    }
}
