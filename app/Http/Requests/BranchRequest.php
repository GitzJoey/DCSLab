<?php

namespace App\Http\Requests;

use App\Rules\validDropDownValue;
use App\Rules\deactivateDefaultCompany;
use Illuminate\Foundation\Http\FormRequest;

class BranchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $nullableArr = [
            'address' => 'nullable',
            'city' => 'nullable',
            'contact' => 'nullable',
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
