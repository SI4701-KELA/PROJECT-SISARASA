<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'buyer_id',
        'seller_id',
        'total_amount',
        'payment_method',
        'payment_proof',
        'status',
        'cancellation_reason',
        'pickup_deadline',
        'pickup_code',
        'voucher_code',
        'discount_amount',
    ];

    protected $casts = [
        'pickup_deadline' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->pickup_code)) {
                $order->pickup_code = self::generateUniquePickupCode();
            }
        });
    }

    public static function generateUniquePickupCode()
    {
        do {
            // SISA-XXXXX where XXXXX is 5 uppercase letters/numbers
            $randomString = strtoupper(substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 5));
            $code = 'SISA-' . $randomString;
        } while (self::where('pickup_code', $code)->exists());

        return $code;
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relasi ke ulasan pesanan ini.
     */
    public function review()
    {
        return $this->hasOne(Review::class);
    }

    /**
     * Cek apakah pesanan ini sudah diulas.
     */
    public function hasReview(): bool
    {
        return $this->review()->exists();
    }

    /**
     * Cek apakah ada komplain aktif (tiket open/diproses) untuk seller pesanan ini.
     */
    public function hasActiveComplaint(): bool
    {
        return \App\Models\Complaint::where('buyer_id', $this->buyer_id)
            ->where('seller_id', $this->seller_id)
            ->whereIn('status_tiket', ['Open', 'Sedang Diproses'])
            ->exists();
    }
}
