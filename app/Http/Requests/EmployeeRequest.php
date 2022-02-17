<?php

namespace App\Http\Requests;

use App\Rules\uniqueCode;
use App\Rules\validDropDownValue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Vinkla\Hashids\Facades\Hashids;

class EmployeeRequest extends FormRequest
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
            case 'store':
                $rules_store = [
                    'company_id' => ['required', 'bail'],
                    'user_id' => ['required', 'bail'],
                ];
                return array_merge($rules_store);
            case 'update':
                $rules_update = [
                    'company_id' => ['required', 'bail'],
                    'user_id' => ['required', 'bail'],
                ];
                return array_merge($rules_update);
            default:
                return [
                    '' => 'required'
                ];
        }
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.company'),
        ];
    }
}
