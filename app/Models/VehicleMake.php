<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleMake extends Model
{
    protected $table = 'vehicle_makes';
    protected $fillable = ['name', 'status'];
}