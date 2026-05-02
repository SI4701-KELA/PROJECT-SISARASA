<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $fillable = ['product_id', 'discount_price', 'is_active'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
