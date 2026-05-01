<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seller;
use Illuminate\Support\Facades\Storage;

class SellerController extends Controller
{
    public function profile(Request $request)
    {
        $seller = Seller::where('user_id', $request->user()->id)->first();
        return view('seller.profile', compact('seller'));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'store_name' => 'required|string',
            'address' => 'required|string',
            'open_time' => 'required',
            'discount_time' => 'required',
            'close_time' => 'required',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'store_photo' => 'nullable|image',
        ]);

        $seller = Seller::where('user_id', $request->user()->id)->first();

        $data = $request->except(['store_photo']);
        $data['user_id'] = $request->user()->id;

        if ($request->hasFile('store_photo')) {
            if ($seller && $seller->store_photo) {
                Storage::disk('public')->delete($seller->store_photo);
            }
            $path = $request->file('store_photo')->store('store_photos', 'public');
            $data['store_photo'] = $path;
        }

        if ($seller) {
            $seller->update($data);
        } else {
            Seller::create($data);
        }

        return redirect()->route('seller.profile')->with('success', 'Profil toko berhasil diperbarui.');
    }
}
