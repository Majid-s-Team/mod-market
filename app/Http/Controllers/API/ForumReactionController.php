<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\ForumCommentReaction;
class ForumReactionController extends Controller
{
    public function toggleReaction($commentId, Request $request)
    {
        $request->validate([
            'reaction' => 'required|string|in:like,love,haha,wow,sad,angry'
        ]);

        $reaction = ForumCommentReaction::where('forum_comment_id', $commentId)
            ->where('user_id', auth()->id())
            ->first();

        if ($reaction) {
            $reaction->delete();
            return response()->json(['reacted' => false]);
        } else {
            ForumCommentReaction::create([
                'forum_comment_id' => $commentId,
                'user_id' => auth()->id(),
                'reaction' => $request->reaction
            ]);

            return response()->json(['reacted' => true]);
        }
    }
}
