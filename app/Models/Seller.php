<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    use HasFactory;

    protected $fillable = ['store_name', 'address', 'latitude', 'longitude', 'status_verified', 'store_photo'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
