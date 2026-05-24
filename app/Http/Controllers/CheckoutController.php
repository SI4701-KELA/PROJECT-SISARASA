<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CheckoutController extends Controller
{
    /**
     * Tampilkan halaman Checkout.
     */
    public function index(Request $request)
    {
        $buyerId = $request->user()->id;

        $cartItems = Cart::where('buyer_id', $buyerId)
            ->with(['product.stock', 'product.discount', 'product.seller', 'product.category'])
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('buyer.cart')
                ->with('error', 'Keranjang Anda kosong. Silakan tambahkan item terlebih dahulu.');
        }

        // Hitung harga efektif dan subtotal
        $grandTotal = 0;
        foreach ($cartItems as $item) {
            if ($item->is_surplus && $item->product->discount) {
                $item->effective_price = $item->product->discount->discount_price;
            } else {
                $item->effective_price = $item->product->base_price;
            }
            $item->subtotal = $item->effective_price * $item->qty;
            $grandTotal += $item->subtotal;
        }

        // Ambil seller dari item pertama (asumsi: satu checkout per toko)
        // Jika multi-seller, group by seller
        $seller = $cartItems->first()->product->seller;

        return view('buyer.checkout', compact('cartItems', 'grandTotal', 'seller'));
    }

    /**
     * Proses pembuatan pesanan (Store Order).
     */
    public function store(Request $request)
    {
        // Validasi payment_method secara strict
        $request->validate([
            'payment_method' => 'required|in:cash,qris',
            'payment_proof' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $paymentMethod = $request->input('payment_method');

        // Jika QRIS, wajib upload bukti transfer
        if ($paymentMethod === 'qris') {
            $request->validate([
                'payment_proof' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            ], [
                'payment_proof.required' => 'Bukti transfer wajib diunggah untuk pembayaran QRIS.',
                'payment_proof.image' => 'Bukti transfer harus berupa file gambar.',
                'payment_proof.mimes' => 'Bukti transfer harus berformat JPG atau PNG.',
                'payment_proof.max' => 'Ukuran file maksimal 2MB.',
            ]);
        }

        $buyerId = $request->user()->id;

        $cartItems = Cart::where('buyer_id', $buyerId)
            ->with(['product.stock', 'product.discount', 'product.seller'])
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('buyer.cart')
                ->with('error', 'Keranjang Anda kosong.');
        }

        // Ambil seller
        $seller = $cartItems->first()->product->seller;

        // Jika QRIS tapi toko belum punya QRIS image
        if ($paymentMethod === 'qris' && empty($seller->qris_image)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['payment_method' => 'Toko ini belum mengatur pembayaran QRIS. Silakan gunakan metode Cash.']);
        }

        DB::beginTransaction();
        try {
            // Hitung total & siapkan order items
            $grandTotal = 0;
            $orderItemsData = [];

            foreach ($cartItems as $item) {
                // Lock stock for update to prevent race conditions
                $stock = \App\Models\Stock::where('product_id', $item->product_id)->lockForUpdate()->first();

                if ($item->is_surplus) {
                    if ($stock->qty_surplus < $item->qty) {
                        throw new \Exception("Stok surplus untuk {$item->product->name} tidak mencukupi.");
                    }
                    $stock->qty_surplus -= $item->qty;
                    if ($item->product->discount) {
                        $price = $item->product->discount->discount_price;
                    } else {
                        $price = $item->product->base_price;
                    }
                } else {
                    if ($stock->qty_reg < $item->qty) {
                        throw new \Exception("Stok reguler untuk {$item->product->name} tidak mencukupi.");
                    }
                    $stock->qty_reg -= $item->qty;
                    $price = $item->product->base_price;
                }
                
                // Save the deducted stock
                $stock->save();

                $subtotal = $price * $item->qty;
                $grandTotal += $subtotal;

                $orderItemsData[] = [
                    'product_id' => $item->product_id,
                    'qty' => $item->qty,
                    'price' => $price,
                    'is_surplus' => $item->is_surplus,
                ];
            }

            // Upload bukti transfer jika QRIS
            $paymentProofPath = null;
            if ($paymentMethod === 'qris' && $request->hasFile('payment_proof')) {
                $paymentProofPath = $request->file('payment_proof')->store('payments', 'public');
            }

            // Tentukan status berdasarkan metode pembayaran
            $status = ($paymentMethod === 'cash') ? 'diproses' : 'menunggu_verifikasi';

            // Buat order
            $order = Order::create([
                'buyer_id' => $buyerId,
                'seller_id' => $seller->id,
                'total_amount' => $grandTotal,
                'payment_method' => $paymentMethod,
                'payment_proof' => $paymentProofPath,
                'status' => $status,
            ]);

            // Buat order items
            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            // ─── POTONG STOK REAL-TIME (dengan Race-Condition Protection) ───
            foreach ($orderItemsData as $itemData) {
                // lockForUpdate() mengunci baris di DB agar tidak bisa dibaca
                // oleh transaksi lain sampai transaksi ini selesai (commit/rollback).
                // Ini mencegah 2 pembeli membeli stok yang sama secara bersamaan.
                $stock = Stock::where('product_id', $itemData['product_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$stock) {
                    throw new \Exception('Data stok tidak ditemukan untuk produk ID ' . $itemData['product_id'] . '.');
                }

                if ($itemData['is_surplus']) {
                    // Jalur SURPLUS: kurangi qty_surplus
                    if ($stock->qty_surplus < $itemData['qty']) {
                        throw new \Exception('Maaf, stok baru saja habis dipesan orang lain.');
                    }
                    $stock->qty_surplus -= $itemData['qty'];
                } else {
                    // Jalur REGULER: kurangi qty_reg
                    if ($stock->qty_reg < $itemData['qty']) {
                        throw new \Exception('Maaf, stok baru saja habis dipesan orang lain.');
                    }
                    $stock->qty_reg -= $itemData['qty'];
                }

                $stock->save();
            }
            // ─────────────────────────────────────────────────────────────────

            // Kosongkan keranjang
            Cart::where('buyer_id', $buyerId)->delete();

            DB::commit();

            return redirect()->route('buyer.checkout.success', $order->id)
                ->with('success', 'Pesanan berhasil dibuat!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal membuat pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Halaman sukses setelah checkout.
     */
    public function success(Request $request, $orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('buyer_id', $request->user()->id)
            ->with(['items.product', 'seller'])
            ->firstOrFail();

        return view('buyer.checkout-success', compact('order'));
    }
}
