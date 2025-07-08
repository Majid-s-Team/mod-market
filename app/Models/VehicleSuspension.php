<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleSuspension extends Model
{
    protected $table = 'vehicle_suspensions';
    protected $fillable = ['name', 'status'];
}