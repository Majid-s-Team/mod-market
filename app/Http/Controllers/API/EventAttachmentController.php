<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EventAttachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponseTrait;
use Log;


class EventAttachmentController extends Controller
{
    use ApiResponseTrait;

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,mp4,mov,pdf,doc,docx,xls,xlsx,webm|max:512000', 
            'type' => 'required|in:image,video'
        ]);
        Log::info('EventAttachmentController: File upload request received', [
            'file_type' => $request->file('file')->getClientOriginalExtension(),
            'type' => $request->type
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
