<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class InboxRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
        
        if (!Auth::check()) return false;
        if (empty(Auth::user()->roles)) return false;

        if (Auth::user()->hasRole(UserRoles::DEVELOPER->value)) return true;

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
            case 'store':
                return [
                    'to' => 'required',
                    'subject' => 'required'
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
