<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'order_id',
        'buyer_id',
        'seller_id',
        'rating',
        'comment',
    ];

    /**
     * Relasi ke pesanan yang dinilai.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relasi ke pengguna/pembeli yang memberikan ulasan.
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Relasi ke toko/UMKM yang dinilai.
     */
    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
}
