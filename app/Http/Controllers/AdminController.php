<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function viewDocument($id)
    {
        $seller = Seller::findOrFail($id);

        if (!$seller->document_path || !Storage::exists($seller->document_path)) {
            abort(404, 'Dokumen tidak ditemukan.');
        }

        return Storage::download($seller->document_path);
    }

    public function stores()
    {
        // Menampilkan penjual yang statusnya bukan 'approved' agar admin bisa memoderasi yang pending/rejected
        $sellers = Seller::orderBy('created_at', 'desc')->get();
        return view('admin.stores', compact('sellers'));
    }

    public function verifySeller(Request $request, $id)
    {
        $seller = Seller::findOrFail($id);
        
        $status_action = $request->input('status_action');
        
        if (in_array($status_action, ['rejected', 'suspended'])) {
            $request->validate([
                'rejection_reason' => 'required|string'
            ]);
            $seller->rejection_reason = $request->input('rejection_reason');
            $seller->verified_at = null;
        } elseif ($status_action === 'approved') {
            $seller->rejection_reason = null;
            $seller->verified_at = now();
        }

        $seller->verification_status = $status_action;
        $seller->save();

        return redirect()->route('admin.stores')->with('success', 'Status penjual berhasil diperbarui.');
    }
}
