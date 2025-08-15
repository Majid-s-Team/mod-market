<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TokenRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_ad_id',
        'buyer_id',
        'seller_id',
        'token_money',
        'concern',
        'reject_reason',
        'status'
    ];

    public function vehicleAd()
    {
        return $this->belongsTo(VehicleAd::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
