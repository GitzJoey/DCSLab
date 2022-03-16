<?php

namespace App\Http\Requests;

use App\Rules\uniqueCode;
use App\Rules\validDropDownValue;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Auth;
use App\Rules\deactivateDefaultCompany;
use Illuminate\Foundation\Http\FormRequest;

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
        $nullableArr = [
            'address' => 'nullable|max:255',
            'city' => 'nullable|max:255',
            'postal_code' => 'nullable',
            'img_path' => 'nullable',
            'remarks' => 'nullable|max:255',
        ];

        $currentRouteMethod = $this->route()->getActionMethod();
        switch($currentRouteMethod) {
            case 'store':
                $rules_store = [
                    'company_id' => ['required', 'bail'],
                    'name' => 'required|min:3|max:255',
                    'email' => 'required|email|max:255',
                    'country' => 'required',
                    'tax_id' => 'required',
                    'ic_num' => 'required|min:12|max:255',
                    'join_date' => 'required',
                    'status' => ['required', new validDropDownValue('ACTIVE_STATUS'), new deactivateDefaultCompany($this->has('default'), $this->input('status'))]
                ];

                return array_merge($rules_store, $nullableArr);
            case 'update':
                $rules_update = [
                    'company_id' => ['required', 'bail'],
                    'name' => 'required|min:3|max:255',
                    'email' => 'required|email|max:255',
                    'country' => 'required',
                    'tax_id' => 'required',
                    'ic_num' => 'required|min:12|max:255',
                    'join_date' => 'required',
                    'status' => ['required', new validDropDownValue('ACTIVE_STATUS'), new deactivateDefaultCompany($this->has('default'), $this->input('status'))]
                ];
                return array_merge($rules_update, $nullableArr);
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
