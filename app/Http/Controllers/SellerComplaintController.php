<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;

class SellerComplaintController extends Controller
{
    /**
     * Menampilkan semua komplain yang masuk ke toko milik seller yang login.
     */
    public function index()
    {
        $seller = auth()->user()->seller;

        // Guard: jika user tidak punya profil seller
        abort_if(!$seller, 403, 'Profil toko tidak ditemukan.');

        $complaints = Complaint::where('seller_id', $seller->id)
            ->with('buyer')
            ->latest()
            ->get();

        return view('seller.complaints', compact('complaints', 'seller'));
    }
}
