<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleRegistrationStatus extends Model
{
    protected $table = 'vehicle_registration_statuses';
    protected $fillable = ['name', 'status'];
}