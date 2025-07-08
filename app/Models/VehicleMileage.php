<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleMileage extends Model
{
    protected $table = 'vehicle_mileages';
    protected $fillable = ['name', 'status'];
}
