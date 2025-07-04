<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\ForumComment;

class ForumCommentController extends Controller
{
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

        return response()->json($comment->load('children', 'reactions'));
    }
}