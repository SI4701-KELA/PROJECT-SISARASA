<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSellerIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $seller = \App\Models\Seller::where('user_id', $request->user()->id)->first();

        if (!$seller || $seller->verification_status !== 'approved') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akun Anda belum diverifikasi oleh Admin.'], 403);
            }

            return redirect()->route('seller.profile')->with('error', 'Akses dibatasi. Akun toko Anda belum diverifikasi atau sedang ditangguhkan.');
        }

        return $next($request);
    }
}
