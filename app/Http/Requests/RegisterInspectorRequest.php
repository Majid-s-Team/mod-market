<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterInspectorRequest extends FormRequest
{
    public function authorize() {
        return true;
    }

    public function rules() {
        return [
            'email' => 'required|email|unique:users,email',
            'contact_number' => 'required|unique:users,contact_number',
            // 'name' => 'required|string|unique:users,name',
            'company_name' => 'required',
            'password' => 'required|confirmed',
            'is_term_accept' => 'required|boolean',
            'id_card_number' => 'required',
            'address' => 'required',
            'street' => 'required',
            'city' => 'required',
            'state' => 'required',
            'service_rate' => 'required|numeric'
        ];
    }
}