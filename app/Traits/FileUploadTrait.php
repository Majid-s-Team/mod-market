<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait FileUploadTrait
{
    public function uploadImage(UploadedFile $file, string $key = 'default')
    {
        $folder = match($key) {
            'profile_image' => 'profiles',
            'event_image' => 'events',
            'post_image' => 'posts',
            default => 'others',
        };

        return $file->store("uploads/{$folder}", 'public');
    }
}
