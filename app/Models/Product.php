<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['seller_id', 'category_id', 'name', 'description', 'base_price', 'image'];

    protected static function booted()
    {
        static::creating(function ($product) {
            if (empty($product->image)) {
                $product->image = 'default.jpg';
            }
        });
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function stock()
    {
        return $this->hasOne(Stock::class);
    }

    public function discount()
    {
        return $this->hasOne(Discount::class);
    }

    public function discounts()
    {
        return $this->hasMany(Discount::class);
    }
}
