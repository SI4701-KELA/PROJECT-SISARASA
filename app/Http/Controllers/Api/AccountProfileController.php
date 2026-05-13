<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\UpdatesUserProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\JsonResponse;

class AccountProfileController extends Controller
{
    use UpdatesUserProfile;


    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = collect($request->validated())->except(['photo'])->all();

        $this->applyProfileFields($user, $validated);

        $previousPhoto = $user->photo;
        if ($request->hasFile('photo')) {
            $user->photo = $this->storeProfilePhoto($request->file('photo'));
        }

        $user->save();

        if ($request->hasFile('photo')) {
            $this->deleteStoredProfilePhoto($previousPhoto);
        }

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
            ],
        ]);
    }
}
