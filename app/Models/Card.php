<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'card_holder',
        'card_number',
        'expiry_month',
        'expiry_year',
        'cvv'
    ];

    protected $casts = [
        'card_holder' => 'encrypted',
        'card_number' => 'encrypted',
        'expiry_month' => 'encrypted',
        'expiry_year' => 'encrypted',
        'cvv' => 'encrypted',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mask sensitive data in API responses.
     */
    public function toArray()
    {
        $array = parent::toArray();


        try {
            $decryptedCardNumber = decrypt($this->card_number);
            $array['card_number'] = '**** **** **** ' . substr($decryptedCardNumber, -4);
        } catch (\Exception $e) {
            $array['card_number'] = '**** **** **** ****';
        }

        try {
            $array['expiry_month'] = decrypt($this->expiry_month);
            $array['expiry_year'] = decrypt($this->expiry_year);
        } catch (\Exception $e) {
            $array['expiry_month'] = 'XX';
            $array['expiry_year'] = 'XXXX';
        }

        unset($array['cvv']);

        return $array;
    }
}
