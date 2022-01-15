<?php

namespace App\Http\Requests;

use App\Rules\uniqueCode;
use App\Rules\validDropDownValue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;

class SupplierRequest extends FormRequest
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
        $companyId = $this->has('company_id') ? Hashids::decode($this['company_id'])[0]:null;

        $nullableArr = [
            'address' => 'nullable',
            'contact' => 'nullable',
            'city' => 'nullable',
            'tax_id' => 'nullable',
            'remarks' => 'nullable',
        ];

        $currentRouteMethod = $this->route()->getActionMethod();
        switch($currentRouteMethod) {
            case 'store':
                $rules_store = [
                    'company_id' => ['required', 'bail'],
                    'code' => ['required', 'max:255', new uniqueCode(table: 'suppliers', companyId: $companyId)],
                    'name' => 'required|max:255',
                    'status' => 'required',
                    'payment_term_type' => 'required',
                    'payment_term' => 'required|numeric',
                    'taxable_enterprise' => 'required',
                ];
                return array_merge($rules_store, $nullableArr);
            case 'update':
                $rules_update = [
                    'company_id' => ['required', 'bail'],
                    'code' => new uniqueCode(table: 'suppliers', companyId: $companyId, exceptId: $this->route('id')),
                    'name' => 'required|max:255',
                    'status' => 'required',
                    'payment_term_type' => 'required',
                    'payment_term' => 'required|numeric',
                    'taxable_enterprise' => 'required',
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
