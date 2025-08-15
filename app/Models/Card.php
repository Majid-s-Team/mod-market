<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type', // card or bank
        'card_holder',
        'card_number',
        'expiry_month',
        'expiry_year',
        'cvv',
        'account_holder',
        'account_number',
        'bank_name',
        'branch_name',
        'ifsc_code'
    ];

    protected $casts = [
        'card_holder' => 'encrypted',
        'card_number' => 'encrypted',
        'expiry_month' => 'encrypted',
        'expiry_year' => 'encrypted',
        'cvv' => 'encrypted',
        'account_holder' => 'encrypted',
        'account_number' => 'encrypted',
        'bank_name' => 'encrypted',
        'branch_name' => 'encrypted',
        'ifsc_code' => 'encrypted',
    ];

    /**
     * Mask sensitive data in API responses.
     */

    public function toArray()
    {
        $array = parent::toArray();

        // Mask card numbers
        if ($this->type === 'card') {
            try {
                $decryptedCardNumber = decrypt($this->card_number);
                $array['card_number'] = '**** **** **** ' . substr($decryptedCardNumber, -4);
            } catch (\Exception $e) {
                $array['card_number'] = '**** **** **** ****';
            }
            unset($array['cvv']);
        }

        // Mask bank account numbers
        if ($this->type === 'bank') {
            try {
                $decryptedAccNumber = decrypt($this->account_number);
                $array['account_number'] = '****' . substr($decryptedAccNumber, -4);
            } catch (\Exception $e) {
                $array['account_number'] = '****';
            }
        }

        return $array;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
