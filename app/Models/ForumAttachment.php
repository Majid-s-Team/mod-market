<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForumAttachment extends Model
{
    protected $fillable = ['forum_post_id', 'file_url'];

    protected $appends = ['full_url'];

    public function post() {
        return $this->belongsTo(ForumPost::class, 'forum_post_id');
    }

    public function getFullUrlAttribute() {
        return asset('storage/' . $this->file_url);
    }
}
