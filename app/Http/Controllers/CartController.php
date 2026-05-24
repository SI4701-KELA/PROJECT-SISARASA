<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Tambahkan item ke keranjang (AJAX).
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1',
            'is_surplus' => 'required|boolean',
        ]);

        $buyerId = $request->user()->id;
        $product = Product::with('stock')->findOrFail($request->product_id);

        // Validasi stok
        if ($request->is_surplus) {
            $maxStock = $product->stock->qty_surplus ?? 0;
        } else {
            $maxStock = $product->stock->qty_reg ?? 0;
        }

        // Cek apakah item sudah ada di keranjang (product + is_surplus yang sama)
        $existingCart = Cart::where('buyer_id', $buyerId)
            ->where('product_id', $request->product_id)
            ->where('is_surplus', $request->is_surplus)
            ->first();

        $currentQty = $existingCart ? $existingCart->qty : 0;
        $newTotalQty = $currentQty + $request->qty;

        if ($newTotalQty > $maxStock) {
            return response()->json([
                'error' => 'Stok tidak mencukupi. Stok tersedia: ' . $maxStock . ', sudah di keranjang: ' . $currentQty . '.'
            ], 422);
        }

        if ($existingCart) {
            $existingCart->qty = $newTotalQty;
            $existingCart->save();
        } else {
            Cart::create([
                'buyer_id' => $buyerId,
                'product_id' => $request->product_id,
                'qty' => $request->qty,
                'is_surplus' => $request->is_surplus,
            ]);
        }

        $cartCount = Cart::where('buyer_id', $buyerId)->count();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil menambahkan ke keranjang!',
            'cart_count' => $cartCount,
        ]);
    }

    /**
     * Tampilkan halaman Keranjang Belanja.
     */
    public function index(Request $request)
    {
        $buyerId = $request->user()->id;

        $cartItems = Cart::where('buyer_id', $buyerId)
            ->with(['product.stock', 'product.discount', 'product.seller', 'product.category'])
            ->get();

        // Hitung harga efektif, subtotal, dan total
        $grandTotal = 0;
        foreach ($cartItems as $item) {
            if ($item->is_surplus && $item->product->discount) {
                $item->effective_price = $item->product->discount->discount_price;
            } else {
                $item->effective_price = $item->product->base_price;
            }
            $item->subtotal = $item->effective_price * $item->qty;

            // Hitung max stock
            if ($item->is_surplus) {
                $item->max_qty = $item->product->stock->qty_surplus ?? 0;
            } else {
                $item->max_qty = $item->product->stock->qty_reg ?? 0;
            }

            $grandTotal += $item->subtotal;
        }

        return view('buyer.cart', compact('cartItems', 'grandTotal'));
    }

    /**
     * Update qty item di keranjang (AJAX).
     */
    public function update(Request $request, $id)
    {
        $cart = Cart::where('id', $id)
            ->where('buyer_id', $request->user()->id)
            ->with('product.stock')
            ->firstOrFail();

        $newQty = (int) $request->input('qty');

        // Validasi minimum
        if ($newQty < 1) {
            return response()->json(['error' => 'Jumlah minimal adalah 1.'], 422);
        }

        // Validasi stok
        if ($cart->is_surplus) {
            $maxStock = $cart->product->stock->qty_surplus ?? 0;
        } else {
            $maxStock = $cart->product->stock->qty_reg ?? 0;
        }

        if ($newQty > $maxStock) {
            return response()->json(['error' => 'Stok tidak mencukupi.'], 422);
        }

        $cart->qty = $newQty;
        $cart->save();

        // Hitung subtotal baru
        if ($cart->is_surplus && $cart->product->discount) {
            $price = $cart->product->discount->discount_price;
        } else {
            $price = $cart->product->base_price;
        }

        return response()->json([
            'success' => true,
            'qty' => $cart->qty,
            'subtotal' => $price * $cart->qty,
        ]);
    }

    /**
     * Hapus item dari keranjang (AJAX).
     */
    public function destroy(Request $request, $id)
    {
        $cart = Cart::where('id', $id)
            ->where('buyer_id', $request->user()->id)
            ->firstOrFail();

        $cart->delete();

        return response()->json(['success' => true]);
    }
}
