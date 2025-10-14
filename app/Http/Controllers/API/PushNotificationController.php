<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\NotificationHelper;
use App\Traits\ApiResponseTrait;

class PushNotificationController extends Controller
{
    use ApiResponseTrait;

    /**
     * Send push notification to one or multiple users
     */
    public function send(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'data' => 'nullable|array',
        ]);

        $title = $request->title;
        $message = $request->message;
        $data = $request->data ?? [];

        $sent = NotificationHelper::sendPushNotification($request->user_ids, $title, $message, $data);

        if (!$sent) {
            return $this->apiResponse('No valid FCM tokens found for selected users.', [], 400);
        }

        return $this->apiResponse('Push notifications sent successfully.', [
            'user_ids' => $request->user_ids,
            'title' => $title,
            'message' => $message,
        ]);
    }
}
