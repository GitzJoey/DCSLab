<?php

namespace App\Http\Requests;

use App\Enums\ActiveStatus;
use App\Enums\ProductType;
use App\Rules\uniqueCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Vinkla\Hashids\Facades\Hashids;

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
        $companyId = $this->has('company_id') ? Hashids::decode($this['company_id'])[0]:null;

        $nullableArr = [
            'remarks' => 'nullable',
            'unit_id.*' => 'nullable',
            'is_base.*' => 'nullable',
            'is_primary_unit.*' => 'nullable',
            'standard_rated_supply' => 'nullable',
            'product_group_id' => 'nullable',
            'product_units_hId.*' => 'nullable',
            'product_units_code.*' => 'nullable',
        ];

        $currentRouteMethod = $this->route()->getActionMethod();
        switch($currentRouteMethod) {
            case 'store':
                $rules_store = [
                    'company_id' => ['required', 'bail'],
                    'code' => ['required', 'max:255', new uniqueCode(table: 'products', companyId: $companyId)],
                    'name' => 'required|min:3|max:255',
                    'brand_id' => 'required',
                    'taxable_supply' => 'required|boolean',
                    'use_serial_number' => 'required|boolean',
                    'price_include_vat' => 'required|boolean',
                    'has_expiry_date' => 'required|boolean',
                    'point' => 'required|numeric|min:0',
                    'status' => [new Enum(ActiveStatus::class)],
                    'product_type' => [new Enum(ProductType::class)],
                    'conv_value.*' => 'numeric|min:1',
                ];
                return array_merge($rules_store, $nullableArr);
            case 'update':
                $rules_update = [
                    'company_id' => ['required', 'bail'],
                    'code' => ['required', 'max:255', new uniqueCode(table: 'products', companyId: $companyId, exceptId: $this->route('id'))],
                    'name' => 'required|min:3|max:255',
                    'brand_id' => 'required',
                    'taxable_supply' => 'required|boolean',
                    'use_serial_number' => 'required|boolean',
                    'price_include_vat' => 'required|boolean',
                    'has_expiry_date' => 'required|boolean',
                    'point' => 'required|numeric|min:0',
                    'status' => [new Enum(ActiveStatus::class)],
                    'product_type' => [new Enum(ProductType::class)],
                    'conv_value.*' => 'numeric|min:1',
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
            'conv_value.*' => trans('validation_attributes.conv_value'),
        ];
    }

    public function validationData()
    {
        $additionalArray = [];

        return array_merge($this->all(), $additionalArray);
    }

    public function prepareForValidation()
    {
        $this->merge([
            'taxable_supply' => $this->has('taxable_supply') ? filter_var($this->taxable_supply, FILTER_VALIDATE_BOOLEAN) : false,
            'use_serial_number' => $this->has('use_serial_number') ? filter_var($this->use_serial_number, FILTER_VALIDATE_BOOLEAN) : false,
            'price_include_vat' => $this->has('price_include_vat') ? filter_var($this->price_include_vat, FILTER_VALIDATE_BOOLEAN) : false,
            'has_expiry_date' => $this->has('has_expiry_date') ? filter_var($this->has_expiry_date, FILTER_VALIDATE_BOOLEAN) : false,
            'product_type' => ProductType::isValid($this->product_type) ? ProductType::to($this->product_type) : null,
            'status' => ActiveStatus::isValid($this->status) ? ActiveStatus::to($this->status) : null
        ]);
    }
}
