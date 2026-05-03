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
        'open_time',
        'discount_time',
        'close_time'
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
