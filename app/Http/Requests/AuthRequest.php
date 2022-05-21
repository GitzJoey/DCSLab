<?php

namespace App\Http\Requests;

use App\Rules\maxTokens;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

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
        $nullableArr = [];
        $currentRouteMethod = $this->route()->getActionMethod();
        switch($currentRouteMethod) {
            case 'auth':
                $rules_auth = [
                    'email' => ['required','email','max:125', new maxTokens($this['email'], 2)],
                    'password' => ['required', 'current_password'],
                ];
                return array_merge($rules_auth, $nullableArr);
            case 'signup':
                $rules_signup = [
                    'name' => ['required', 'max:125'],
                    'email' => ['required', 'max:125', 'email', 'unique:users,email'],
                    'password' => ['required', 'max:125', 'confirmed'],
                    'password_confirmation' => ['required', 'max:125']
                ];
                return array_merge($rules_signup, $nullableArr);
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

    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();

        throw new HttpResponseException(
            response()->json(['errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
