<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleEngineModification extends Model
{
    protected $table = 'vehicle_engine_modifications';
    protected $fillable = ['name', 'status'];
}
