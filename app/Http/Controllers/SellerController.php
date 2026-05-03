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
        if ($request->has('latitude')) {
            $request->merge(['latitude' => str_replace(',', '.', $request->latitude)]);
        }
        if ($request->has('longitude')) {
            $request->merge(['longitude' => str_replace(',', '.', $request->longitude)]);
        }

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

    public function products(Request $request)
    {
        $seller = Seller::where('user_id', $request->user()->id)->first();
        if (!$seller) {
            return redirect()->route('seller.profile')->with('error', 'Silakan lengkapi profil toko terlebih dahulu.');
        }

        $products = $seller->products()->with(['stock', 'category'])->get();
        $categories = \App\Models\Category::all();

        return view('seller.products', compact('products', 'categories'));
    }

    public function storeProduct(Request $request)
    {
        $seller = Seller::where('user_id', $request->user()->id)->first();
        if (!$seller) {
            return redirect()->back()->with('error', 'Silakan lengkapi profil toko terlebih dahulu sebelum menambahkan produk.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'base_price' => 'required|numeric|min:0',
            'discount_price' => 'required|numeric|min:0',
            'qty_reg' => 'required|integer|min:1',
            'image' => 'required|image|max:2048',
        ]);

        $imagePath = $request->file('image')->store('products', 'public');

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $product = \App\Models\Product::create([
                'seller_id' => $seller->id,
                'category_id' => $request->category_id,
                'name' => $request->name,
                'description' => $request->description,
                'base_price' => $request->base_price,
                'image' => $imagePath,
            ]);

            \App\Models\Stock::create([
                'product_id' => $product->id,
                'qty_reg' => $request->qty_reg,
                'qty_surplus' => 0,
            ]);

            \App\Models\Discount::create([
                'product_id' => $product->id,
                'discount_price' => $request->discount_price,
                'is_active' => false,
            ]);

            \Illuminate\Support\Facades\DB::commit();
            return redirect()->back()->with('success', 'Produk berhasil ditambahkan.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menambahkan produk: ' . $e->getMessage());
        }
    }

    public function toggleDiscount(Request $request, $id)
    {
        $seller = Seller::where('user_id', $request->user()->id)->first();
        if (!$seller) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $product = \App\Models\Product::where('id', $id)
                                      ->where('seller_id', $seller->id)
                                      ->firstOrFail();

        $discount = $product->discount()->first();
        if ($discount) {
            $discount->update([
                'is_active' => !$discount->is_active
            ]);
            $status = $discount->is_active ? 'diaktifkan' : 'dinonaktifkan';
            return redirect()->back()->with('success', "Diskon Sisa Rasa berhasil $status.");
        }

        return redirect()->back()->with('error', 'Data diskon tidak ditemukan.');
    }
}
