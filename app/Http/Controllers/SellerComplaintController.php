<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SellerComplaintController extends Controller
{
    /**
     * Menampilkan semua komplain yang masuk ke toko milik seller yang login.
     * Rute: GET /seller/complaints (seller.complaints.index)
     */
    public function index()
    {
        $seller = auth()->user()->seller;

        // Guard: jika user tidak punya profil seller
        abort_if(!$seller, 403, 'Profil toko tidak ditemukan.');

        $complaints = Complaint::where('seller_id', $seller->id)
            ->with('buyer')
            ->latest()
            ->get();

        return view('seller.complaints.index', compact('complaints', 'seller'));
    }

    /**
     * Menampilkan detail satu tiket komplain untuk ditinjau Seller.
     * Rute: GET /seller/complaints/{id} (seller.complaints.show)
     *
     * Keamanan: Seller hanya boleh melihat komplain yang ditujukan ke tokonya sendiri.
     */
    public function show($id)
    {
        $seller = auth()->user()->seller;
        abort_if(!$seller, 403, 'Profil toko tidak ditemukan.');

        // Ambil komplain dan validasi kepemilikan (anti-IDOR)
        $complaint = Complaint::with(['buyer', 'seller'])->findOrFail($id);

        // Pastikan komplain ini memang milik toko yang sedang login
        abort_if(
            $complaint->seller_id !== $seller->id,
            403,
            'Anda tidak memiliki akses ke tiket ini.'
        );

        return view('seller.complaints.show', compact('complaint', 'seller'));
    }

    /**
     * Memproses respons Seller terhadap tiket komplain.
     * Rute: POST /seller/complaints/{id}/respond (seller.complaints.respond)
     *
     * Alur:
     *  - 'approved': Tiket langsung Selesai (klaim pembeli diterima).
     *  - 'rejected': Tiket berubah ke 'Open' agar Admin dapat memediasi.
     *               Seller wajib menyertakan alasan dan foto bukti kelayakan produk.
     */
    public function respond(Request $request, $id)
    {
        $seller = auth()->user()->seller;
        abort_if(!$seller, 403, 'Profil toko tidak ditemukan.');

        $complaint = Complaint::findOrFail($id);

        // Pastikan komplain ini milik toko seller yang login
        abort_if(
            $complaint->seller_id !== $seller->id,
            403,
            'Anda tidak memiliki akses ke tiket ini.'
        );

        // Guard: hanya bisa merespons jika masih menunggu konfirmasi seller
        abort_if(
            $complaint->status_tiket !== 'menunggu_seller',
            403,
            'Tiket ini sudah direspons sebelumnya dan tidak dapat diubah lagi.'
        );

        // Validasi input dengan aturan kondisional berdasarkan seller_action
        $validated = $request->validate([
            'seller_action' => 'required|in:approved,rejected',
            // seller_reason wajib diisi minimal 10 karakter jika seller menolak
            'seller_reason' => 'required_if:seller_action,rejected|nullable|string|min:10|max:2000',
            // Foto bukti wajib diunggah jika seller menolak (maks 2MB)
            'seller_proof'  => 'required_if:seller_action,rejected|nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'seller_action.required'        => 'Pilih salah satu tindakan (Setuju atau Tolak).',
            'seller_action.in'              => 'Pilihan tindakan tidak valid.',
            'seller_reason.required_if'     => 'Alasan sanggahan wajib diisi jika Anda menolak komplain.',
            'seller_reason.min'             => 'Alasan sanggahan minimal 10 karakter.',
            'seller_proof.required_if'      => 'Foto bukti kelayakan produk wajib diunggah jika Anda menolak komplain.',
            'seller_proof.image'            => 'File yang diunggah harus berupa gambar (JPG, PNG, WebP).',
            'seller_proof.max'              => 'Ukuran foto bukti maksimal 2MB.',
        ]);

        // Siapkan data yang akan disimpan
        $updateData = [
            'seller_action'       => $validated['seller_action'],
            'seller_responded_at' => now(),
        ];

        if ($validated['seller_action'] === 'approved') {
            // Seller menyetujui klaim pembeli → tiket selesai tanpa perlu mediasi Admin
            $updateData['status_tiket']   = 'Selesai';
            $updateData['seller_reason']  = 'Seller menyetujui klaim pengembalian dana pembeli.';

        } elseif ($validated['seller_action'] === 'rejected') {
            // Seller menolak → tiket kembali ke 'Open' agar Admin dapat memediasi
            $updateData['status_tiket']  = 'Open';
            $updateData['seller_reason'] = $validated['seller_reason'];

            // Simpan foto bukti kelayakan produk dari Seller ke storage publik
            if ($request->hasFile('seller_proof')) {
                $updateData['seller_proof_path'] = Storage::disk('public')->put(
                    'seller_dispute_proofs',
                    $request->file('seller_proof')
                );
            }
        }

        $complaint->update($updateData);

        $pesan = $validated['seller_action'] === 'approved'
            ? '✅ Anda telah menyetujui klaim pembeli. Tiket ditandai Selesai.'
            : '📋 Sanggahan Anda telah dikirim. Admin akan memediasi sengketa ini.';

        return redirect()
            ->route('seller.complaints.index')
            ->with('success', $pesan);
    }
}

