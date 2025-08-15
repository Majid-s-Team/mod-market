<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionReport extends Model
{
    protected $fillable = [
        'inspection_request_id',
        'engine_test', 'engine_description',
        'transmission_test', 'transmission_description',
        'braking_system_test', 'braking_system_description',
        'suspension_system_test', 'suspension_system_description',
        'interior_exterior_test', 'interior_exterior_description',
        'tyre_vehicle_test', 'tyre_vehicle_description',
        'computer_electronics_test', 'computer_electronics_description',
        'average_score',
        'final_remarks'
    ];

    public function inspectionRequest()
    {
        return $this->belongsTo(InspectionRequest::class);
    }
}
