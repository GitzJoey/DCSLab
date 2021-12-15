<?php

namespace App\Http\Requests;

use App\Rules\uniqueCode;
use App\Rules\deactivateDefaultCompany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

use App\Rules\validDropDownValue;

class CompanyRequest extends FormRequest
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
        $userId = Auth::id();

        $companyId = $this['company_id'];

        $nullableArr = [
            'address' => 'nullable',
        ];

        $currentRouteMethod = $this->route()->getActionMethod();
        switch($currentRouteMethod) {
            case 'store':
                $rules_store = [
                    'code' => ['required', 'max:255'],
                    'name' => 'required|max:255',
                    'status' => ['required', new validDropDownValue('ACTIVE_STATUS'), new deactivateDefaultCompany($this->has('default'), $this->input('status'))]
                ];
                return array_merge($rules_store, $nullableArr);
            case 'update':
                $rules_update = [
                    'code' => ['required', 'max:255'],
                    'name' => 'required|max:255',
                    'status' => ['required', new validDropDownValue('ACTIVE_STATUS'), new deactivateDefaultCompany($this->has('default'), $this->input('status'))]
                ];
                return array_merge($rules_update, $nullableArr);
            default:
                return [
                    '' => 'required'
                ];
        }
    }
}
