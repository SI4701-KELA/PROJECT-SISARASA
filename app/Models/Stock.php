<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = ['product_id', 'qty_reg', 'qty_surplus'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
