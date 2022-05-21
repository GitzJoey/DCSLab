<?php

namespace App\Http\Requests;

use App\Rules\maxTokens;
use Illuminate\Foundation\Http\FormRequest;

class AuthRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $currentRouteMethod = $this->route()->getActionMethod();
        switch($currentRouteMethod) {
            case 'auth':
                return [
                    'email' => ['required','email','max:125', new maxTokens($this['email'], 2)],
                    'password' => ['required', 'current_password'],
                ];
            case 'signup':
                return [
                    'name' => ['required', 'max:125'],
                    'email' => ['required', 'max:125', 'email', 'unique:users,email'],
                    'password' => ['required', 'max:125', 'confirmed'],
                    'password_confirmation' => ['required', 'max:125']
                ];
            default:
                return [
                    '' => 'required'
                ];
        }
    }

    public function attributes()
    {
        return [
        ];
    }

    public function validationData()
    {
        $additionalArray = [];

        return array_merge($this->all(), $additionalArray);
    }

    public function prepareForValidation()
    {
        $this->merge([
        ]);
    }
}
