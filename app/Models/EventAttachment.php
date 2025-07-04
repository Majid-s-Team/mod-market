<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventAttachment extends Model
{
    protected $fillable = ['event_id', 'url', 'type'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
