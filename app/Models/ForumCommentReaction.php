<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForumCommentReaction extends Model
{
    protected $fillable = ['forum_comment_id', 'user_id', 'type','reaction'];

    public function comment() {
        return $this->belongsTo(ForumComment::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}