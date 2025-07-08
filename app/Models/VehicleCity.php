<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class VehicleCity extends Model
{
    protected $fillable = ['name', 'status', 'state_id'];

    public function state()
    {
        return $this->belongsTo(VehicleState::class, 'state_id');
    }
}