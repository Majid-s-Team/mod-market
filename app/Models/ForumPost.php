<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ForumPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'description', 'privacy', 'is_draft'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function attachments() {
        return $this->hasMany(ForumAttachment::class);
    }

public function comments()
{
    return $this->hasMany(ForumComment::class)->whereNull('parent_id');
}


    public function likes() {
        return $this->hasMany(ForumLike::class);
    }

}