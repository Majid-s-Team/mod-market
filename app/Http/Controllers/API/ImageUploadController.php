<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\FileUploadTrait;
use App\Traits\ApiResponseTrait;

class ImageUploadController extends Controller
{
    use FileUploadTrait, ApiResponseTrait;

    public function upload(Request $request)
    {
        $request->validate([
            'key' => 'required|in:profile_image,event_image,post_image,certificate,business_license_image',
            'image' => 'required|image|max:2048',
        ]);

        $url = $this->uploadImage($request->file('image'), $request->key);

        return $this->apiResponse('Image uploaded successfully', [
            'url' => asset('storage/' . $url)
        ]);
    }
}
