<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    public function update(ChangePasswordRequest $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        if (! Hash::check($request->input('current_password'), $user->getAuthPassword())) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Kata sandi lama salah.',
                    'errors' => [
                        'current_password' => ['Kata sandi lama salah.'],
                    ],
                ], 401);
            }

            throw tap(
                ValidationException::withMessages([
                    'current_password' => ['Kata sandi lama salah.'],
                ]),
                fn (ValidationException $e) => $e->errorBag('updatePassword')
            );
        }

        $user->password = $request->validated('password');
        $user->save();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Kata sandi berhasil diperbarui.']);
        }

        return back()->with('status', 'password-updated');
    }
}
