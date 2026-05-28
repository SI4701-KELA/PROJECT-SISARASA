<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'store_name', 
        'address', 
        'latitude', 
        'longitude', 
        'verification_status', 
        'rejection_reason',
        'document_path',
        'verified_at',
        'store_photo',
        'qris_image',
        'open_time',
        'discount_time',
        'close_time',
        'pending_profile_updates',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'pending_profile_updates' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Relasi ke record favorit yang merujuk toko ini.
     */
    public function favoriteStores()
    {
        return $this->hasMany(\App\Models\FavoriteStore::class);
    }

    /**
     * Relasi many-to-many ke User yang memfavoritkan toko ini.
     */
    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'favorite_stores', 'seller_id', 'user_id')->withTimestamps();
    }

    /**
     * Relasi ke komplain yang diajukan buyer terhadap toko ini.
     */
    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    /**
     * Relasi ke seluruh ulasan pelanggan toko ini.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
