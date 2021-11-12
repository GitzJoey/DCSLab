<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $currentRouteMethod = $this->route()->getActionMethod();

        switch($currentRouteMethod) {
            case 'updateProfile':
                return [];
            case 'changePassword':
                return [
                    'current_password' => 'required',
                    'password' => 'required|confirmed',
                    'password_confirmation' => 'required',
                ];
            default:
                return [
                    //
                ];
        }

    }
}
