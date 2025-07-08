<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleBrakes extends Model
{
    protected $table = 'vehicle_brakes';
    protected $fillable = ['name', 'status'];
}