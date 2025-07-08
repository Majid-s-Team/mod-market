<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleInteriorExterior extends Model
{
    protected $table = 'vehicle_interior_exteriors';
    protected $fillable = ['name', 'status'];
}
