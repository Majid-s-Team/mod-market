<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehiclePerformanceTuning extends Model
{
    protected $table = 'vehicle_performance_tunings';
    protected $fillable = ['name', 'status'];
}