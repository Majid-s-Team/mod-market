<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleYear extends Model
{
    protected $table = 'vehicle_years';
    protected $fillable = ['name', 'status'];
}