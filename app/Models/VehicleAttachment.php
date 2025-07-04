<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleAttachment extends Model
{
    use HasFactory;

    protected $fillable = ['vehicle_ad_id', 'file_path'];

    public function vehicleAd()
    {
        return $this->belongsTo(VehicleAd::class);
    }
}