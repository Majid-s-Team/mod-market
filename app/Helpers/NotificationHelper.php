<?php

namespace App\Helpers;

use App\Models\Notification;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class NotificationHelper
{
    public static function sendTemplateNotification($userId, $templateKey, $data = [], $extraData = [])
    {
        $template = NotificationTemplate::getTemplate($templateKey, $data);

        if (!$template) return false;

        $title = $template['title'];
        $message = $template['message'];

        $notification = Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $templateKey,
            'data' => array_merge($data, $extraData),
        ]);

        self::sendPushNotification($userId, $title, $message, $extraData);

        return $notification;
    }

    public static function sendPushNotification($userIds, $title, $message, $data = [])
    {
        $serverKey = config('services.fcm.server_key');

        // Allow single ID or array of IDs
        $userIds = is_array($userIds) ? $userIds : [$userIds];

        $tokens = User::whereIn('id', $userIds)
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) {
            return false;
        }

        $payload = [
            'registration_ids' => $tokens,
            'notification' => [
                'title' => $title,
                'body' => $message,
                'sound' => 'default',
            ],
            'data' => $data
        ];

        Http::withHeaders([
            'Authorization' => 'key=' . $serverKey,
            'Content-Type' => 'application/json'
        ])->post('https://fcm.googleapis.com/fcm/send', $payload);

        foreach ($userIds as $userId) {
            Notification::create([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => 'push',
                'data' => $data,
            ]);
        }

        return true;
    }
}
