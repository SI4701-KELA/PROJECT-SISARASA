<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait UpdatesUserProfile
{
    protected function applyProfileFields(User $user, array $data): void
    {
        if (array_key_exists('name', $data)) {
            $user->name = $data['name'];
        }
        if (array_key_exists('phone', $data)) {
            $user->phone = $data['phone'] === '' ? null : $data['phone'];
        }
    }

    protected function storeProfilePhoto(UploadedFile $file): string
    {
        return $file->store('profiles', 'public');
    }

    protected function deleteStoredProfilePhoto(?string $relativePath): void
    {
        if ($relativePath && Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }
    }
}
