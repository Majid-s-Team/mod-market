<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ForumComment;
use App\Traits\ApiResponseTrait;

class ForumCommentController extends Controller
{
    use ApiResponseTrait;

    public function store(Request $request, $postId)
    {
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

        return $this->apiResponse('Comment added successfully', [
            'comment' => $comment->load('children', 'reactions')
        ]);
    }
}
