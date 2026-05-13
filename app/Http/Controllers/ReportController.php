<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Report;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'seller_id' => 'required|exists:sellers,id',
            'kategori' => 'required|string',
            'deskripsi' => 'required|string',
            'foto_bukti' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'kategori.required' => 'Kategori wajib diisi.',
            'deskripsi.required' => 'Deskripsi wajib diisi.',
            'foto_bukti.image' => 'File harus berupa gambar JPG atau PNG.',
            'foto_bukti.mimes' => 'File harus berupa gambar JPG atau PNG.',
            'foto_bukti.max' => 'Ukuran file foto maksimal 2MB.',
        ]);

        $buyerId = auth()->id();
        $sellerId = $request->seller_id;

        // Pencegahan Spam: Max 1 laporan per toko dalam 24 jam
        $recentReport = Report::where('buyer_id', $buyerId)
            ->where('seller_id', $sellerId)
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->first();

        if ($recentReport) {
            return back()->with('error', 'Anda sudah melaporkan toko ini. Silakan tunggu 24 jam untuk mengirim laporan baru.');
        }

        $report = new Report();
        $report->buyer_id = $buyerId;
        $report->seller_id = $sellerId;
        $report->kategori = $request->kategori;
        $report->deskripsi = $request->deskripsi;

        if ($request->hasFile('foto_bukti')) {
            $path = $request->file('foto_bukti')->store('reports', 'public');
            $report->foto_bukti = $path;
        }

        $report->save();

        return back()->with('success', 'Terima kasih, laporan Anda telah diterima dan akan ditinjau oleh Admin.');
    }
}
