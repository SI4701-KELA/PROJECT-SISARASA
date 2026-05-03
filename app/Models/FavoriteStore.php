<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FavoriteStore extends Model
{
    protected $fillable = ['user_id', 'seller_id'];

    /**
     * Relasi ke User (buyer yang memfavoritkan).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Seller (toko yang difavoritkan).
     */
    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
}
