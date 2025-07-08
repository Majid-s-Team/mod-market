<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleFuelType extends Model
{
    protected $table = 'vehicle_fuel_types';
    protected $fillable = ['name', 'status'];
}