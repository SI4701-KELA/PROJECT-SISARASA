<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'code',
        'type',
        'value',
        'min_order',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::retrieved(function ($voucher) {
            if ($voucher->is_active && $voucher->expires_at && $voucher->expires_at->isPast()) {
                $voucher->is_active = false;
                $voucher->save();
            }
        });
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
}
