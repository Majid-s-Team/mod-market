<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EventAttachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EventAttachmentController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,mp4|max:5120',
            'type' => 'required|in:image,video'
        ]);

        $path = $request->file('file')->store('event_attachments', 'public');
        $url = asset('storage/' . $path);

        return response()->json([
            'message' => 'Uploaded',
            'url' => $url,
            'type' => $request->type
        ]);
    }

    public function destroy($id)
    {
        $attachment = EventAttachment::findOrFail($id);
        \Storage::disk('public')->delete($attachment->url);
        $attachment->delete();
        return response()->json(['message' => 'Attachment deleted']);
    }

}
