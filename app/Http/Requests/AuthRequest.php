<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // cualquiera puede autenticarse/registrarse
    }

    public function rules(): array
    {
        $rules = [
            'email'    => 'required|email',
            'password' => 'required',
        ];

        // Si la ruta es "register", agregamos reglas extra
        if ($this->routeIs('register')) {
            $rules['name']     = 'required|string|max:255';
            $rules['password'] = 'required|string|min:8';
            $rules['email']    = 'required|email|unique:users'; // unique solo en registro
            $rules['phone']    = 'nullable|string';
            $rules['role']     = 'nullable|in:customer,vendor,courier';
        }

        return $rules;
    }
}