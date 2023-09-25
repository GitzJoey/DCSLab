<?php

namespace App\Http\Requests;

use App\Models\Profile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UserProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        $currentRouteMethod = $this->route()->getActionMethod();

        switch ($currentRouteMethod) {
            case 'update':
                return $user->can('update', Profile::class) ? true : false;
            default:
                return false;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $currentRouteMethod = $this->route()->getActionMethod();

        switch ($currentRouteMethod) {
            case 'update':
                return [
                    'name' => 'required|alpha_num',

                    'first_name' => 'nullable',
                    'last_name' => 'nullable',
                    'address' => 'nullable',
                    'city' => 'nullable',
                    'postal_code' => 'alpha_dash|min:5|max:10',
                    'country' => 'nullable',
                    'tax_id' => 'required',
                    'ic_num' => 'required',
                    'remarks' => 'nullable',

                    'roles' => 'required',

                    'theme' => 'required',
                    'date_format' => 'required',
                    'time_format' => 'required',
                    'api_token' => 'nullable',

                    'current_password' => 'required',
                    'password' => 'required|confirmed',
                    'password_confirmation' => 'required',
                ];
            default:
                return [
                    '' => 'required',
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
