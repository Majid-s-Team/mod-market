<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleAd extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'make_id',
        'model_id',
        'year_id',
        'mileage_id',
        'fuel_type_id',
        'transmission_type_id',
        'city_id',
        'state_id',
        'registration_status_id',
        'is_modified',
        'modification_details',
        'engine_modification_id',
        'exhaust_system_id',
        'suspension_id',
        'wheels_tires_id',
        'brakes_id',
        'body_kit_id',
        'interior_upgrade_id',
        'performance_tuning_id',
        'electronics_id',
        'interior_exterior_id',
        'description',
        'price',
        'is_featured',
        'status'
    ];

    public function attachments()
    {
        return $this->hasMany(VehicleAttachment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function make()
    {
        return $this->belongsTo(VehicleMake::class, 'make_id');
    }

    public function model()
    {
        return $this->belongsTo(VehicleModel::class, 'model_id');
    }

    public function year()
    {
        return $this->belongsTo(VehicleYear::class, 'year_id');
    }

    public function mileage()
    {
        return $this->belongsTo(VehicleMileage::class, 'mileage_id');
    }

    public function fuelType()
    {
        return $this->belongsTo(VehicleFuelType::class, 'fuel_type_id');
    }

    public function transmissionType()
    {
        return $this->belongsTo(VehicleTransmissionType::class, 'transmission_type_id');
    }

    public function city()
    {
        return $this->belongsTo(VehicleCity::class, 'city_id');
    }

    public function state()
    {
        return $this->belongsTo(VehicleState::class, 'state_id');
    }

    public function registrationStatus()
    {
        return $this->belongsTo(VehicleRegistrationStatus::class, 'registration_status_id');
    }

    public function engineModification()
    {
        return $this->belongsTo(VehicleEngineModification::class, 'engine_modification_id');
    }

    public function exhaustSystem()
    {
        return $this->belongsTo(VehicleExhaustSystem::class, 'exhaust_system_id');
    }

    public function suspension()
    {
        return $this->belongsTo(VehicleSuspension::class, 'suspension_id');
    }

    public function wheelsTires()
    {
        return $this->belongsTo(VehicleWheelsTires::class, 'wheels_tires_id');
    }

    public function brakes()
    {
        return $this->belongsTo(VehicleBrakes::class, 'brakes_id');
    }

    public function bodyKit()
    {
        return $this->belongsTo(VehicleBodyKit::class, 'body_kit_id');
    }

    public function interiorUpgrade()
    {
        return $this->belongsTo(VehicleInteriorUpgrade::class, 'interior_upgrade_id');
    }

    public function performanceTuning()
    {
        return $this->belongsTo(VehiclePerformanceTuning::class, 'performance_tuning_id');
    }

    public function electronics()
    {
        return $this->belongsTo(VehicleElectronics::class, 'electronics_id');
    }

    public function interiorExterior()
    {
        return $this->belongsTo(VehicleInteriorExterior::class, 'interior_exterior_id');
    }
   public function getAllRelations()
{
    return [
        'attachments',
        'user:id,name,profile_image',

        'make:id,name',
        'model:id,name',
        'year:id,name',
        'mileage:id,name',
        'fuelType:id,name',
        'transmissionType:id,name',
        'registrationStatus:id,name',
        'engineModification:id,name',
        'exhaustSystem:id,name',
        'suspension:id,name',
        'wheelsTires:id,name',
        'brakes:id,name',
        'bodyKit:id,name',
        'interiorUpgrade:id,name',
        'performanceTuning:id,name',
        'electronics:id,name',
        'interiorExterior:id,name',

        'city:id,name',
        'state:id,name'
    ];
}

}
