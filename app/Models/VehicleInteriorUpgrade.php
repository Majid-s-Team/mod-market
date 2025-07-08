<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleInteriorUpgrade extends Model
{
    protected $table = 'vehicle_interior_upgrades';
    protected $fillable = ['name', 'status'];
}