<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EventAttachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponseTrait;


class EventAttachmentController extends Controller
{
    use ApiResponseTrait;

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,mp4|max:5120',
            'type' => 'required|in:image,video'
        ]);

        $path = $request->file('file')->store('event_attachments', 'public');
        $url = asset('storage/' . $path);

        return $this->apiResponse('File uploaded successfully', [
            'url' => $url,
            'type' => $request->type
        ]);
    }

    public function destroy($id)
    {
        $attachment = EventAttachment::find($id);

        if (!$attachment) {
            return $this->apiError('Attachment not found', [], 404);
        }

        Storage::disk('public')->delete($attachment->url);
        $attachment->delete();

        return $this->apiResponse('Attachment deleted successfully');
    }
}
