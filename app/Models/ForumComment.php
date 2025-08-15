<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForumComment extends Model
{
    protected $fillable = ['forum_post_id', 'user_id', 'comment', 'parent_id'];

    public function post() {
        return $this->belongsTo(ForumPost::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function parent() {
        return $this->belongsTo(ForumComment::class, 'parent_id');
    }

    public function children() {
        return $this->hasMany(ForumComment::class, 'parent_id');
    }

    public function reactions() {
        return $this->hasMany(ForumCommentReaction::class);
    }
    public function replies() {
        return $this->hasMany(ForumComment::class, 'parent_id')->with('user');
    }
}