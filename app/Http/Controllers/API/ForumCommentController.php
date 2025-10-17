<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ForumComment;
use App\Models\ForumPost;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponseTrait;
use App\Helpers\NotificationHelper;


class ForumCommentController extends Controller
{
    use ApiResponseTrait;

    public function store(Request $request, $postId)
    {
    $user = Auth::user();

        $request->validate([
            'comment' => 'required|string',
            'parent_id' => 'nullable|exists:forum_comments,id'
        ]);

        $comment = ForumComment::create([
            'forum_post_id' => $postId,
            'user_id' => auth()->id(),
            'comment' => $request->comment,
            'parent_id' => $request->parent_id
        ]);
        $post = ForumPost::where('id',$postId)->first();

          if ($request->filled('parent_id')) {
             NotificationHelper::sendTemplateNotification(
                    $post->user_id,
                    'commentReplyPost',
                    ['username' => $user->name],
                    ['post_id'=>$comment->forum_post_id,'user_id'=>$user->id,'name'=>$user->name,'role'=>$user->role,'profile_image'=>$user->profile_image]
                );

                 NotificationHelper::sendTemplateNotification(
                    $comment->user_id,
                    'commentReply',
                    ['username' => $user->name],
                    ['comment_id'=>$comment->id,'post_id'=>$comment->forum_post_id,'user_id'=>$user->id,'name'=>$user->name,'role'=>$user->role,'profile_image'=>$user->profile_image]
                );
    } else {
            NotificationHelper::sendTemplateNotification(
                    $post->user_id,
                    'comment',
                    ['username' => $user->name],
                    ['post_id'=>$comment->forum_post_id,'user_id'=>$user->id,'name'=>$user->name,'role'=>$user->role,'profile_image'=>$user->profile_image]
                );
    }

        return $this->apiResponse('Comment added successfully', [
            'comment' => $comment->load('children', 'reactions')
        ]);
    }
}
