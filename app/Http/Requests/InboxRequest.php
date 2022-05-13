<?php

namespace App\Http\Requests;

use App\Enums\UserRoles;
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
        if (!Auth::check()) return false;
        
        /** @var \App\User */
        $user = Auth::user();
        
        if (empty($user->roles)) return false;

        if ($user->hasRole(UserRoles::DEVELOPER->value)) return true;

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
