<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AccountPasswordController extends Controller
{
    public function update(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! Hash::check($request->input('current_password'), $user->getAuthPassword())) {
            return response()->json([
                'message' => 'Kata sandi lama salah.',
                'errors' => [
                    'current_password' => ['Kata sandi lama salah.'],
                ],
            ], 401);
        }

        $user->password = $request->validated('password');
        $user->save();

        return response()->json([
            'message' => 'Kata sandi berhasil diperbarui.',
        ]);
    }
}
