<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleBodyKit extends Model
{
    protected $table = 'vehicle_body_kits';
    protected $fillable = ['name', 'status'];
}
