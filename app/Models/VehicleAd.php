<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleAd extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'make', 'model', 'year', 'mileage', 'fuel_type', 'transmission_type',
        'city', 'state', 'registration_status', 'is_modified', 'modification_details',
        'engine_modification', 'exhaust_system', 'suspension', 'wheels_tires', 'brakes',
        'body_kit', 'interior_upgrade', 'performance_tuning', 'electronics', 'description',
        'price', 'is_featured', 'status'
    ];

    public function attachments()
    {
        return $this->hasMany(VehicleAttachment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}