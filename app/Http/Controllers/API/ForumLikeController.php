<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ForumPost;
use App\Models\ForumLike;
use Illuminate\Support\Facades\Auth;


class ForumLikeController extends Controller
{
    public function toggleLike($postId)
    {
        $post = ForumPost::findOrFail($postId);

        $like = $post->likes()->where('user_id', auth()->id())->first();

        if ($like) {
            $like->delete();
            return response()->json(['liked' => false]);
        } else {
            $post->likes()->create(['user_id' => auth()->id()]);
            return response()->json(['liked' => true]);
        }
    }
}