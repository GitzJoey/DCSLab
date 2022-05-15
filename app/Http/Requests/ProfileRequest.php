<?php

namespace App\Http\Requests;

use App\Enums\UserRoles;
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
        if (!Auth::check()) return false;
        
        /** @var \App\User */
        $user = Auth::user();
        
        if (empty($user->roles)) return false;

        if ($user->hasRole(UserRoles::DEVELOPER->value)) return true;
        
        if ($user->hasPermission('update-profile')) return true;

        return false;
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
                return [
                    'name' => 'nullable',
                    'first_name' => 'nullable',
                    'last_name' => 'nullable',
                    'address' => 'nullable',
                    'city' => 'nullable',
                    'postal_code' => 'numeric|max:10',
                    'country' => 'nullable',
                    'tax_id' => 'required',
                    'ic_num' => 'required',
                    'remarks' => 'nullable',
                ];
            case 'updateRoles':
                return [
                    'roles' => 'required'
                ];
            case 'updateSettings':
                return [
                    'theme' => 'required',
                    'dateFormat' => 'required',
                    'timeFormat' =>  'required',
                    'apiToken' => 'nullable',
                ];
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
