<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ForumPost;
use App\Models\ForumLike;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponseTrait;

class ForumLikeController extends Controller
{
    use ApiResponseTrait;

    public function toggleLike($postId)
    {
        $post = ForumPost::find($postId);

        if (!$post) {
            return $this->apiError('Forum post not found', [], 404);
        }

        $like = $post->likes()->where('user_id', auth()->id())->first();

        if ($like) {
            $like->delete();
            return $this->apiResponse('Like removed successfully', [
                'liked' => false
            ]);
        } else {
            $post->likes()->create(['user_id' => auth()->id()]);
            return $this->apiResponse('Post liked successfully', [
                'liked' => true
            ]);
        }
    }
}
