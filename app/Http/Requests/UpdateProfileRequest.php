<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize() {
        return true;
    }

    protected function prepareForValidation()
    {
        if (is_string($this->profile_image)) {
            $this->merge(['profile_image_url' => $this->profile_image]);
            $this->offsetUnset('profile_image');
        }
    }

    public function rules() {
        return [
            'name' => 'sometimes|required',
            'company_name' => 'nullable|string',
            'contact_number' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'profile_image' => 'nullable|image', 
            'profile_image_url' => 'nullable|string|url', 
        ];
    }
}
