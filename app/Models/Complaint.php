<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = [
        'seller_id',
        'buyer_id',
        'kategori_masalah',
        'deskripsi',
        'foto_bukti',
        'balasan_admin',
        'status_tiket',
        // PBI-20: Kolom respons Seller untuk alur konfirmasi & mediasi
        'seller_action',
        'seller_reason',
        'seller_proof_path',
        'seller_responded_at',
    ];

    /**
     * Cast otomatis untuk tipe data kolom tertentu.
     */
    protected $casts = [
        'seller_responded_at' => 'datetime',
    ];

    /**
     * Relasi ke Toko (Seller) yang dikomplain.
     */
    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    /**
     * Relasi ke Pembeli yang mengajukan komplain.
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
}
