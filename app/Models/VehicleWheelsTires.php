<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleWheelsTires extends Model
{
    protected $table = 'vehicle_wheels_tires';
    protected $fillable = ['name', 'status'];
}