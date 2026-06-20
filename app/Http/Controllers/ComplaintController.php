<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ComplaintController extends Controller
{
    /**
     * Form pengajuan komplain ke sebuah toko.
     * TC-CMP-001 & TC-CMP-002: Toko harus approved, buyer tidak bisa komplain toko orang lain.
     */
    public function create(Seller $seller)
    {
        // TC-CMP-001: Hanya toko yang sudah approved yang bisa dikomplain
        abort_if($seller->verification_status !== 'approved', 403, 'Toko ini tidak dapat dikomplain.');

        // TC-CMP-002: Buyer harus punya minimal 1 pesanan "Selesai" di toko ini
        $hasOrder = \App\Models\Order::where('buyer_id', auth()->id())
            ->where('seller_id', $seller->id)
            ->where('status', 'selesai')
            ->exists();

        if (!$hasOrder) {
            return redirect()->route('buyer.store.show', $seller->id)->with('error', 'Anda tidak dapat mengajukan komplain karena tidak memiliki riwayat pesanan yang sudah selesai di toko ini.');
        }

        // Cek apakah buyer sudah pernah mengajukan komplain yang masih aktif ke toko ini.
        // Status 'menunggu_seller' dan 'Open' dianggap masih aktif.
        $existingComplaint = Complaint::where('seller_id', $seller->id)
            ->where('buyer_id', auth()->id())
            ->whereIn('status_tiket', ['menunggu_seller', 'Open', 'Sedang Diproses'])
            ->first();

        return view('buyer.complaints.create', compact('seller', 'existingComplaint'));
    }

    /**
     * Simpan komplain baru ke database.
     * TC-CMP-003: Foto wajib jika kategori Kualitas Buruk/Basi.
     * TC-CMP-004: buyer_id diambil dari auth, bukan dari request.
     */
    public function store(Request $request, Seller $seller)
    {
        // TC-CMP-001: Guard ulang dari sisi POST
        abort_if($seller->verification_status !== 'approved', 403, 'Toko ini tidak dapat dikomplain.');

        // TC-CMP-003: Validasi dinamis — foto WAJIB jika kategori Kualitas Buruk/Basi
        $validated = $request->validate([
            'kategori_masalah' => 'required|in:Pesanan Tidak Sesuai,Porsi Kurang,Kualitas Buruk/Basi,Lainnya',
            'deskripsi'        => 'required|string|min:20|max:2000',
            'foto_bukti'       => 'required_if:kategori_masalah,Kualitas Buruk/Basi|nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'deskripsi.min'           => 'Deskripsi harus minimal 20 karakter agar keluhan dapat ditindaklanjuti.',
            'foto_bukti.required_if'  => 'Foto bukti wajib diunggah untuk kategori Kualitas Buruk/Basi.',
            'foto_bukti.image'        => 'File yang diunggah harus berupa gambar (JPG, PNG, WebP).',
            'foto_bukti.max'          => 'Ukuran foto maksimal 2MB.',
        ]);

        // Simpan foto ke disk public jika ada
        $fotoPath = null;
        if ($request->hasFile('foto_bukti')) {
            $fotoPath = Storage::disk('public')->put('complaints', $request->file('foto_bukti'));
        }

        // TC-CMP-004: buyer_id WAJIB dari auth(), bukan request input
        // Status awal 'menunggu_seller' — Seller harus mengonfirmasi terlebih dahulu
        // sebelum Admin dapat memediasi (alur baru PBI-20).
        Complaint::create([
            'seller_id'        => $seller->id,
            'buyer_id'         => auth()->id(),
            'kategori_masalah' => $validated['kategori_masalah'],
            'deskripsi'        => $validated['deskripsi'],
            'foto_bukti'       => $fotoPath,
            'status_tiket'     => 'menunggu_seller',
        ]);

        return redirect()->route('buyer.complaints.index')
            ->with('success', '✅ Komplain berhasil diajukan! Tim kami akan meninjau dalam 1x24 jam.');
    }

    /**
     * Daftar semua komplain milik buyer yang sedang login.
     * TC-CMP-006: Data terisolasi per user.
     */
    public function index()
    {
        $complaints = Complaint::where('buyer_id', auth()->id())
            ->with('seller')
            ->latest()
            ->get();

        return view('buyer.complaints.index', compact('complaints'));
    }
}
