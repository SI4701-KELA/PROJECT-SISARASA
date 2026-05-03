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
            'store_name'    => 'required|string',
            'address'       => 'required|string',
            'open_time'     => 'required',
            'discount_time' => 'required',
            'close_time'    => 'required',
            'latitude'      => 'nullable|numeric',
            'longitude'     => 'nullable|numeric',
            'store_photo'   => 'nullable|image',
        ]);

        $seller = Seller::where('user_id', $request->user()->id)->first();

        // --- Handle foto toko (langsung diterapkan, tidak dimoderasi) ---
        $photoPart = [];
        if ($request->hasFile('store_photo')) {
            if ($seller && $seller->store_photo) {
                Storage::disk('public')->delete($seller->store_photo);
            }
            $path = $request->file('store_photo')->store('store_photos', 'public');
            $photoPart = ['store_photo' => $path];
        }

        // --- Seller APPROVED: karantina perubahan data toko ke pending queue ---
        if ($seller && $seller->verification_status === 'approved') {

            $pendingData = [
                'store_name'    => $request->store_name,
                'address'       => $request->address,
                'latitude'      => $request->latitude,
                'longitude'     => $request->longitude,
                'open_time'     => $request->open_time,
                'discount_time' => $request->discount_time,
                'close_time'    => $request->close_time,
                'requested_at'  => now()->toDateTimeString(),
            ];

            $seller->pending_profile_updates = $pendingData;

            // Foto tetap langsung disimpan
            if (!empty($photoPart)) {
                $seller->store_photo = $photoPart['store_photo'];
            }

            $seller->save();

            return redirect()->route('seller.profile')
                ->with('success', '⏳ Usulan perubahan nama profil / letak warung sedang diantrekan untuk ditinjau oleh pimpinan Admin.');
        }

        // --- Seller PENDING / REJECTED: simpan langsung (data belum ter-approve) ---
        $data = $request->except(['store_photo']);
        $data['user_id'] = $request->user()->id;

        if (!empty($photoPart)) {
            $data['store_photo'] = $photoPart['store_photo'];
        }

        if ($seller) {
            $seller->update($data);
        } else {
            Seller::create($data);
        }

        return redirect()->route('seller.profile')->with('success', 'Profil toko berhasil diperbarui.');
    }

    public function uploadDocuments(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $seller = Seller::where('user_id', $request->user()->id)->first();
        if (!$seller) {
            $seller = Seller::create([
                'user_id' => $request->user()->id,
                'verification_status' => 'pending'
            ]);
        }

        if ($request->hasFile('document')) {
            if ($seller->document_path) {
                Storage::delete($seller->document_path);
            }
            $path = $request->file('document')->store('documents');
            $seller->update([
                'document_path' => $path,
                'verification_status' => 'pending',
                'verified_at' => null,
                'rejection_reason' => null
            ]);
        }

        return redirect()->route('seller.profile')->with('success', 'Dokumen berhasil diunggah. Akun Anda sedang menunggu verifikasi Admin.');
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

    public function updateProduct(Request $request, $id)
    {
        $seller = Seller::where('user_id', $request->user()->id)->first();
        if (!$seller) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $product = \App\Models\Product::where('seller_id', $seller->id)->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'base_price' => 'required|numeric|min:0',
            'discount_price' => 'required|numeric|min:0',
            'qty_reg' => 'required|integer|min:1',
            'image' => 'nullable|image|max:2048',
        ]);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $data = [
                'category_id' => $request->category_id,
                'name' => $request->name,
                'description' => $request->description,
                'base_price' => $request->base_price,
            ];

            if ($request->hasFile('image')) {
                if ($product->image) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image);
                }
                $data['image'] = $request->file('image')->store('products', 'public');
            }

            $product->update($data);

            if ($product->stock) {
                $product->stock->update(['qty_reg' => $request->qty_reg]);
            } else {
                \App\Models\Stock::create([
                    'product_id' => $product->id,
                    'qty_reg' => $request->qty_reg,
                    'qty_surplus' => 0,
                ]);
            }

            $discount = $product->discount()->first();
            if ($discount) {
                $discount->update(['discount_price' => $request->discount_price]);
            } else {
                \App\Models\Discount::create([
                    'product_id' => $product->id,
                    'discount_price' => $request->discount_price,
                    'is_active' => false,
                ]);
            }

            \Illuminate\Support\Facades\DB::commit();
            return redirect()->back()->with('success', 'Produk berhasil diperbarui.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui produk: ' . $e->getMessage());
        }
    }

    public function destroyProduct(Request $request, $id)
    {
        $seller = Seller::where('user_id', $request->user()->id)->first();
        if (!$seller) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $product = \App\Models\Product::where('seller_id', $seller->id)->findOrFail($id);

        if ($product->image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->back()->with('success', 'Produk berhasil dihapus.');
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
