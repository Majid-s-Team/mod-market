<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleExhaustSystem extends Model
{
    protected $table = 'vehicle_exhaust_systems';
    protected $fillable = ['name', 'status'];
}