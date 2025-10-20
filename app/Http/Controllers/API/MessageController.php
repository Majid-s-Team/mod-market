<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\VehicleAd;
use App\Models\User;
use App\Traits\ApiResponseTrait;

class MessageController extends Controller
{
    use ApiResponseTrait;

    public function socketStore(Request $request)
    {
        $validated = $request->validate([
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id',
            'vehicle_ad_id' => 'nullable|exists:vehicle_ads,id',
            'message' => 'nullable|string',
            'message_type' => 'nullable|string|in:text,image,video,file,emoji,link',
            'media_url' => 'nullable|string',
        ]);

        if (empty($validated['message'] ?? null) && empty($validated['media_url'] ?? null)) {
            return $this->apiResponse('Either message or media_url is required', null, 422);
        }

        $msg = Message::create([
            'sender_id' => $validated['sender_id'],
            'receiver_id' => $validated['receiver_id'],
            'vehicle_ad_id' => $validated['vehicle_ad_id'] ?? null,
            'message' => $validated['message'] ?? '',
            'message_type' => $validated['message_type'] ?? 'text',
            'media_url' => $validated['media_url'] ?? '',
            'status' => 'sent',
        ]);

        $msg->load(['sender:id,name,profile_image,email', 'receiver:id,name,profile_image,email']);

        return $this->apiResponse('Message sent successfully', $msg, 201);
    }


    public function chatHistory($user1, $user2)
    {
        $messages = Message::with(['sender:id,name,profile_image,email', 'receiver:id,name,profile_image,email','vehicleAd'])
            ->where(function ($q) use ($user1, $user2) {
                $q->where('sender_id', $user1)->where('receiver_id', $user2);
            })
            ->orWhere(function ($q) use ($user1, $user2) {
                $q->where('sender_id', $user2)->where('receiver_id', $user1);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return $this->apiResponse('Chat history loaded', $messages);
    }

    public function unseenMessages($user_id)
    {
        $messages = Message::with(['sender:id,name,profile_image,email', 'receiver:id,name,profile_image,email','vehicleAd'])
            ->where('receiver_id', $user_id)
            ->where('status', 'sent')
            ->orderByDesc('created_at')
            ->get();

        Message::whereIn('id', $messages->pluck('id'))->update(['status' => 'delivered']);

        return $this->apiResponse('Unseen messages fetched', $messages);
    }

    public function markSeen(Request $request)
    {
        $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'exists:messages,id',
        ]);

        Message::whereIn('id', $request->message_ids)->update(['status' => 'read']);

        return $this->apiResponse('Messages marked as read');
    }

    public function inbox($user_id)
    {
        $inbox = Message::selectRaw('
                CASE
                    WHEN sender_id = ? THEN receiver_id
                    ELSE sender_id
                END as chat_with_id,
                MAX(created_at) as last_message_time
            ', [$user_id])
            ->where(function ($q) use ($user_id) {
                $q->where('sender_id', $user_id)->orWhere('receiver_id', $user_id);
            })
            ->groupBy('chat_with_id')
            ->with(['sender:id,name,profile_image,email', 'receiver:id,name,profile_image,email'])
            ->get()
            ->map(function ($chat) use ($user_id) {
                $lastMsg = Message::where(function ($q) use ($user_id, $chat) {
                    $q->where('sender_id', $user_id)->where('receiver_id', $chat->chat_with_id);
                })
                ->orWhere(function ($q) use ($user_id, $chat) {
                    $q->where('sender_id', $chat->chat_with_id)->where('receiver_id', $user_id);
                })
                ->latest()
                ->first();

    $unreadCount = Message::where('sender_id', $user_id)
    ->where('receiver_id', $chat->chat_with_id)
    ->where('status', '!=', 'read')
    ->count();

                return [
                    'chat_with' => $lastMsg->sender_id == $user_id ? $lastMsg->receiver : $lastMsg->sender,
                    'last_message' => $lastMsg->message,
                    'message_type' => $lastMsg->message_type ?? 'text',
                    'time' => $lastMsg->created_at->format('H:i'),
                    'date' => $lastMsg->created_at->format('Y-m-d'),
                    'unreadCount'  => $unreadCount, // Added

                ];
            });

        return $this->apiResponse('Inbox loaded', $inbox);
    }
    public function uploadMedia(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,mp4,mov,avi,pdf,doc,docx,zip|max:20480', // 20MB max
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id',
        ]);

        $file = $request->file('file');
        $type = $file->getMimeType();

        if (str_contains($type, 'image')) {
            $messageType = 'image';
        } elseif (str_contains($type, 'video')) {
            $messageType = 'video';
        } else {
            $messageType = 'file';
        }

        $path = $file->store('chat_media', 'public');
        $mediaUrl = asset('storage/' . $path);

        $msg = Message::create([
            'sender_id' => $request->sender_id,
            'receiver_id' => $request->receiver_id,
            'message' => $file->getClientOriginalName(),
            'message_type' => $messageType,
            'media_url' => $mediaUrl,
            'status' => 'sent',
        ]);

        $msg->load(['sender:id,name,profile_image,email', 'receiver:id,name,profile_image,email']);

        return $this->apiResponse('Media uploaded successfully', $msg, 201);
    }


}
