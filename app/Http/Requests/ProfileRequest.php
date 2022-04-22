<?php

namespace App\Http\Requests;

use App\Enums\ActiveStatus;
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
            'status' => ActiveStatus::isValid($this->status) ? ActiveStatus::fromName($this->status)->value : -1
        ]);
    }
}
