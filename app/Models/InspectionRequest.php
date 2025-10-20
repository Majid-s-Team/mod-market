<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InspectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'inspector_id',
        'vehicle_ad_id',
        'full_name',
        'phone_number',
        'city_id',
        'state_id',
        'inspection_date',
        'inspection_time',
        'inspection_date_start',
        'inspection_date_end',
        'inspection_time_start',
        'inspection_time_end',
        'want_test_drive',
        'description',
        'inspector_price',
        'status',
        'payment_status',
        'payment_reference',
        'type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function vehicleAd()
    {
        return $this->belongsTo(VehicleAd::class);
    }

    public function city()
    {
        return $this->belongsTo(VehicleCity::class);
    }

    public function state()
    {
        return $this->belongsTo(VehicleState::class);
    }
     public function inspectionReports()
    {
        return $this->hasMany(InspectionReport::class, 'inspection_request_id');
    }
    public function reviews()
    {
        return $this->hasMany(Review:: class, 'inspection_request_id');
    }
}
