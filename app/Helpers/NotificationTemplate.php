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
