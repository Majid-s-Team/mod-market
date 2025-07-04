<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\ForumAttachment;

class ForumAttachmentController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,mp4,pdf,docx|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store('forum_attachments', 'public');
        $url = asset('storage/' . $path);

        return response()->json([
            'message' => 'File uploaded successfully',
            'url' => $url,
        ], 201);
    }
    public function destroy($id)
    {
        $attachment = ForumAttachment::findOrFail($id);
        $file = storage_path('app/public/' . $attachment->file_url);

        if (file_exists($file)) {
            unlink($file);
        }

        $attachment->delete();

        return response()->json(['message' => 'Attachment deleted']);
    }

}
