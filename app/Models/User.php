<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;



class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles;

    protected $guard_name = 'web';

    protected $fillable = [
        'name',
        'company_name',
        'email',
        'contact_number',
        'password',
        'is_term_accept',
        'business_license_image',
        'id_card_number',
        'address',
        'cover_photo',
        'street',
        'city',
        'state',
        'service_rate',
        'certificate',
        'profile_image',
        'otp',
        'otp_expire_at',
        'role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp'
    ];

    protected $casts = [
        'otp_expire_at' => 'datetime',
        'is_term_accept' => 'boolean',
        'service_rate' => 'decimal:2'
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function vehicleAds()
    {
        return $this->hasMany(VehicleAd::class);
    }

}