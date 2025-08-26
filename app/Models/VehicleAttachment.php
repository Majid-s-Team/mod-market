<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class VehicleAttachment extends Model
{
    use HasFactory;

    protected $fillable = ['vehicle_ad_id', 'file_path'];
    protected $appends = ['file_url'];

    public function vehicleAd()
    {
        return $this->belongsTo(VehicleAd::class);
    }

    public function getFileUrlAttribute()
    {
         return Storage::disk('public')->url($this->file_path);
    }
}
