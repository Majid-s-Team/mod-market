<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'vehicle_ad_id',
        'message',
        'status',
        'media_url',
        'message_type',
    ];

    // 🔹 User who sent the message
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // 🔹 User who received the message
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // 🔹 Related vehicle ad (optional)
    public function vehicleAd()
    {
        return $this->belongsTo(VehicleAd::class, 'vehicle_ad_id', 'id');
    }
}
