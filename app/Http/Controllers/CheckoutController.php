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

        $vouchers = \App\Models\Voucher::where('seller_id', $seller->id)
            ->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->get();

        return view('buyer.checkout', compact('cartItems', 'grandTotal', 'seller', 'vouchers'));
    }

    /**
     * Proses pembuatan pesanan (Store Order).
     */
    public function store(Request $request)
    {
        // Validasi request secara strict
        $request->validate([
            'payment_method' => 'required|in:cash,qris',
            'payment_proof' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'promo_code' => 'nullable|string',
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
                if ($item->is_surplus && $item->product->discount) {
                    $price = $item->product->discount->discount_price;
                } else {
                    $price = $item->product->base_price;
                }

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

            // Tentukan status pesanan
            $status = $paymentMethod === 'cash' ? 'diproses' : 'menunggu_verifikasi';

            // Hitung potongan voucher jika ada
            $discountAmount = 0;
            $voucherCode = null;

            if ($request->filled('promo_code')) {
                $code = strtoupper(trim($request->input('promo_code')));
                $voucher = \App\Models\Voucher::where('code', $code)
                    ->where('seller_id', $seller->id)
                    ->where('is_active', true)
                    ->where(function($q) {
                        $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                    })
                    ->first();

                if (!$voucher) {
                    throw new \Exception('Kode promo tidak valid untuk toko ini.');
                }

                if ($grandTotal < $voucher->min_order) {
                    throw new \Exception('Minimum pembelian Rp ' . number_format($voucher->min_order, 0, ',', '.') . ' tidak terpenuhi.');
                }

                if ($voucher->type === 'fixed') {
                    $discountAmount = $voucher->value;
                } else if ($voucher->type === 'percent') {
                    $discountAmount = (int) (($grandTotal * $voucher->value) / 100);
                }

                $discountAmount = min($discountAmount, $grandTotal);
                $grandTotal = max(0, $grandTotal - $discountAmount);
                $voucherCode = $voucher->code;
            }

            // Buat order
            $order = Order::create([
                'buyer_id' => $buyerId,
                'seller_id' => $seller->id,
                'total_amount' => $grandTotal,
                'payment_method' => $paymentMethod,
                'payment_proof' => $paymentProofPath,
                'status' => $status,
                'voucher_code' => $voucherCode,
                'discount_amount' => $discountAmount,
            ]);

            // Buat order items
            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            // ─── POTONG STOK REAL-TIME (dengan Race-Condition Protection) ───
            foreach ($orderItemsData as $itemData) {
                $stock = Stock::where('product_id', $itemData['product_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$stock) {
                    throw new \Exception('Data stok tidak ditemukan untuk produk ID ' . $itemData['product_id'] . '.');
                }

                if ($itemData['is_surplus']) {
                    if ($stock->qty_surplus < $itemData['qty']) {
                        throw new \Exception('Maaf, stok baru saja habis dipesan orang lain.');
                    }
                    $stock->qty_surplus -= $itemData['qty'];
                } else {
                    if ($stock->qty_reg < $itemData['qty']) {
                        throw new \Exception('Maaf, stok baru saja habis dipesan orang lain.');
                    }
                    $stock->qty_reg -= $itemData['qty'];
                }

                $stock->save();
            }

            // Kosongkan keranjang
            Cart::where('buyer_id', $buyerId)->delete();

            DB::commit();

            return redirect()->route('buyer.checkout.success', $order->id)
                ->with('success', 'Pesanan berhasil dibuat!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
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

    /**
     * Cek kevalidan voucher via AJAX.
     */
    public function checkVoucher(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $buyerId = $request->user()->id;
        $cartItems = Cart::where('buyer_id', $buyerId)
            ->with('product.discount')
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang kosong.'
            ], 422);
        }

        $seller = $cartItems->first()->product->seller;
        if (!$seller) {
            return response()->json([
                'success' => false,
                'message' => 'Toko tidak ditemukan.'
            ], 422);
        }

        // Hitung total belanja
        $subtotal = 0;
        foreach ($cartItems as $item) {
            if ($item->is_surplus && $item->product->discount) {
                $price = $item->product->discount->discount_price;
            } else {
                $price = $item->product->base_price;
            }
            $subtotal += $price * $item->qty;
        }

        $code = strtoupper(trim($request->input('code')));
        $voucher = \App\Models\Voucher::where('code', $code)
            ->where('seller_id', $seller->id)
            ->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Kode voucher tidak valid untuk toko ini.'
            ], 422);
        }

        if ($subtotal < $voucher->min_order) {
            return response()->json([
                'success' => false,
                'message' => 'Minimum pembelian Rp ' . number_format($voucher->min_order, 0, ',', '.') . ' tidak terpenuhi.'
            ], 422);
        }

        $discount = 0;
        if ($voucher->type === 'fixed') {
            $discount = $voucher->value;
        } else if ($voucher->type === 'percent') {
            $discount = (int) (($subtotal * $voucher->value) / 100);
        }

        $discount = min($discount, $subtotal);

        return response()->json([
            'success' => true,
            'discount' => $discount,
            'message' => 'Voucher berhasil digunakan! Potongan Rp ' . number_format($discount, 0, ',', '.')
        ]);
    }
}
