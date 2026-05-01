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
        'status_verified', 
        'store_photo',
        'open_time',
        'discount_time',
        'close_time'
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
