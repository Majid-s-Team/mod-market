<?php

namespace App\Helpers;

class NotificationTemplate
{
    public static function getTemplate($key, $data = [])
    {
        $templates = [
            'forum_like' => [
                'title' => 'New Like on Your Post',
                'message' => ':username liked your post.',
            ],
            'forum_comment' => [
                'title' => 'New Comment',
                'message' => ':username commented on your post: ":comment"',
            ],
            'new_follower' => [
                'title' => 'New Follower',
                'message' => ':username started following you.',
            ],
             'selfInspection' => [
                'title' => 'New Inspection Request',
                'message' => ':username has made an insecption request on your Vehcile Ad.',
             ],
             'vendorInspection' => [
                'title' => 'New Vendor Inspection Request',
                'message' => ':username has made an insecption request on your Vehcile Ad.',
             ],
             'toVendorInspection' => [
                'title' => 'New Inspection Request',
                'message' => ':username has requested you to perform an inspection on a Vehicle',
             ],
             'requestStatus' => [
                'title' => 'Inspection Status Updated',
                'message' => ':username has responded to your inspection request.',
            ],
             'inspectionReport' => [
                'title' => 'New Inspection Report',
            'message' => ':username has finished inspecting your vehicle. You can now view the inspection report.'
            ],
             'reportToOwner' => [
                'title' => 'New Inspection Report',
            'message' => ':username has finished inspecting your vehicle. You can now view the inspection report.'
            ],
             'tokenRequest' => [
                'title' => 'New Token Request',
                'message' => ':username has just sent you a token amount for your vehicle ad.'
            ],
             'tokenStatus' => [
                'title' => 'Token Status Update',
                'message' => ':username has responded to your token amount request for the vehicle ad.'
            ],
             'comment' => [
                'title' => 'New Comment Added',
                'message' => ':username has just commented on your Forum Post.'
            ],
             'commentReply' => [
                'title' => 'Reply to comment Added',
                'message' => ':username has just replied to your comment'
            ]
            ,
             'commentReplyPost' => [
                'title' => 'Reply to comment Added',
                'message' => ':username has just replied to a comment on your Forum Post.'
            ],
             'reviewRating' => [
                'title' => 'New Review Added',
                'message' => ':username has rated your inspection report.',
            ]
            ,
             'messageReceived' => [
                'title' => 'New Message',
                'message' => ':username has just sent you a message.',
            ]

        ];


        if (!isset($templates[$key])) {
            return null;
        }

        $template = $templates[$key];
        foreach ($data as $key => $value) {
            $template['title'] = str_replace(":$key", $value, $template['title']);
            $template['message'] = str_replace(":$key", $value, $template['message']);
        }

        return $template;
    }
}
