<?php

namespace App\Helpers;

use App\Models\Notification;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as PushNotification;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Factory;

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

        self::sendFcmNotification($userId, $title, $message, $templateKey, $data);

        // self::sendPushNotification($userId, $title, $message, $extraData);

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

    public static  function  sendFcmNotification($userIds, $title, $message, $type, $data)
    {
        // $tokens = ['ek7m45vTdEoDsCrW531L8N:APA91bGdWNXsLnxO6mbMLRstWPwZOfchfXQi6kWCugc7rVaa2vxyHKRNyZP7KS_lysBr95gxFO8HOljQc4RSA2llqgYjAB7MLvGpGlPAzX4X4a1makv6Ehk'];
        $userIds = is_array($userIds) ? $userIds : [$userIds];

        $tokens = User::whereIn('id', $userIds)
            ->whereNotNull('device_token')
            ->pluck('device_token')
            ->toArray();

        $notification_data = [
            'notification' => [
                'title'    => $title,
                'body'     => $message,
                'sound'    => 'default',
                // 'badge'    => 3, 
                // 'priority' => 'high', 
            ],
            'data' => [
                'title' => $title,
                'body'  => $message,
                'type'  => $type,
                'user_badge'  => 3,
                'custom_data' => json_encode($data),
            ]
        ];

        $firebase = (new Factory)
            ->withServiceAccount(public_path('moddedmarket-8f439-firebase-adminsdk-fbsvc-dea29aa2c8.json'));

        $messaging = $firebase->createMessaging();
        $config = ApnsConfig::fromArray([
            'payload' => [
                'aps' => [
                    'badge' => $notification_data['notification']['badge'],
                    'sound' => 'noti.wav',
                ],
            ],
        ]);
        $android = AndroidConfig::fromArray([

            'priority' => 'normal',
            'notification' => [
                'sound' => 'noti.wav',
            ],
        ]);
        $message = CloudMessage::fromArray($notification_data);
        $message = $message->withApnsConfig($config)->withAndroidConfig($android);
        $report = $messaging->sendMulticast($message, $tokens);
        if ($report->hasFailures()) {
            $error_msg = '';
            foreach ($report->failures()->getItems() as $failure) {
                $error_msg .= $failure->error()->getMessage() . PHP_EOL;
            }
            // file_put_contents(base_path('notification-error.txt'),$error_msg);
        }
    }
}
