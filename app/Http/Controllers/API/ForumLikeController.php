<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ForumPost;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Auth;
use App\Helpers\NotificationHelper;

class ForumLikeController extends Controller
{
    use ApiResponseTrait;

    public function toggleLike($postId)
    {
        $post = ForumPost::find($postId);
            $user = Auth::user();


        if (!$post) {
            return $this->apiError('Forum post not found', [], 404);
        }

        $userId = auth()->id();
        $userName = auth()->user()->name;

        $like = $post->likes()->where('user_id', $userId)->first();

        if ($like) {
            $like->delete();

            return $this->apiResponse('Like removed successfully', [
                'liked' => false
            ]);
        } else {
            $post->likes()->create(['user_id' => $userId]);

            if ($post->user_id !== $userId) {
                NotificationHelper::sendTemplateNotification(
                    $post->user_id,
                    'forum_like',
                    ['username' => $userName],
                    ['post_id' => $post->id, 'liked_by' => $userId,'user_id'=>$user->id,'name'=>$user->name,'role'=>$user->role,'profile_image'=>$user->profile_image]
                );
            }

            return $this->apiResponse('Post liked successfully', [
                'liked' => true
            ]);
        }
    }
}
