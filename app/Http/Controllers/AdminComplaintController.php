<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;

class AdminComplaintController extends Controller
{
    /**
     * Menampilkan semua tiket komplain untuk dashboard admin.
     */
    public function index()
    {
        $complaints = Complaint::with(['seller', 'buyer'])->latest()->get();

        return view('admin.complaints.index', compact('complaints'));
    }

    /**
     * Admin membalas dan/atau mengubah status tiket komplain.
     * TC-CMP-005: Update balasan + status.
     * TC-CMP-007: Tiket yang sudah "Selesai" tidak bisa diubah lagi.
     */
    public function update(Request $request, $id)
    {
        $complaint = Complaint::findOrFail($id);

        // TC-CMP-007: Locking — tiket Selesai bersifat final
        abort_if(
            $complaint->status_tiket === 'Selesai',
            403,
            'Tiket yang sudah Selesai tidak dapat diubah lagi!'
        );

        $validated = $request->validate([
            'balasan_admin' => 'required|string|min:10|max:2000',
            'status_tiket'  => 'required|in:Open,Sedang Diproses,Selesai',
        ], [
            'balasan_admin.required' => 'Balasan admin wajib diisi sebelum mengubah status.',
            'balasan_admin.min'      => 'Balasan minimal 10 karakter.',
        ]);

        // TC-CMP-005: Update data ke database
        $complaint->update([
            'balasan_admin' => $validated['balasan_admin'],
            'status_tiket'  => $validated['status_tiket'],
        ]);

        return redirect()->route('admin.complaints.index')
            ->with('success', '✅ Tiket #' . $complaint->id . ' berhasil diperbarui.');
    }
}
