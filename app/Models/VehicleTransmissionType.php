<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleTransmissionType extends Model
{
    protected $table = 'vehicle_transmission_types';
    protected $fillable = ['name', 'status'];
}