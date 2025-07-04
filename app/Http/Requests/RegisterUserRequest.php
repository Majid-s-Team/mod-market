<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
{
    public function authorize() {
        return true;
    }

    public function rules() {
        return [
            'email' => 'required|email|unique:users,email',
            'contact_number' => 'required|unique:users,contact_number',
            'name' => 'required|string|unique:users,name',
            'password' => 'required|confirmed',
            'is_term_accept' => 'required|boolean',
        ];
    }
}