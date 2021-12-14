<?php

namespace App\Http\Requests;

use App\Rules\uniqueCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ProductRequest extends FormRequest
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
            '' => 'nullable',
        ];

        $currentRouteMethod = $this->route()->getActionMethod();
        switch($currentRouteMethod) {
            case 'store':
                $rules_store = [
                    'code' => ['required', 'max:255', new uniqueCode(table: 'products', userId: $userId, companyId: $companyId)],
                    'name' => 'required|min:3|max:255',
                    'product_group_id' => 'required',
                    'brand_id' => 'required',
                    'unit_id' => 'required',
                    'status' => 'required',    
                ];
                return array_merge($rules_store, $nullableArr);
            case 'update':
                $rules_update = [
                    'code' => ['required', 'max:255', new uniqueCode(table: 'products', userId: $userId, companyId: $companyId, exceptId: $this->route('id'))],
                    'name' => 'required|min:3|max:255',
                    'product_group_id' => 'required',
                    'unit_id' => 'required',
                    'status' => 'required',        
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
