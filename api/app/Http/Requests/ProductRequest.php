<?php

namespace App\Http\Requests;

use App\Enums\ProductCategory;
use App\Enums\ProductType;
use App\Enums\RecordStatus;
use App\Models\Product;
use App\Rules\isValidCompany;
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
        if (!Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $product = $this->route('product');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'list':
                return $user->can('viewAny', Product::class) ? true : false;
            case 'read':
                return $user->can('view', Product::class, $product) ? true : false;
            case 'store':
                return $user->can('create', Product::class) ? true : false;
            case 'update':
                return $user->can('update', Product::class, $product) ? true : false;
            case 'delete':
                return $user->can('delete', Product::class, $product) ? true : false;
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
        $nullableArr = [
            'brand_id' => 'nullable',
            'standard_rated_supply' => 'nullable',
            'remarks' => 'nullable',
            'product_units_id.*' => 'nullable',
            'product_units_remarks.*' => 'nullable',
        ];

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'list':
                $rules_list = [
                    'company_id' => ['required', new isValidCompany(), 'bail'],
                    'productCategory' => ['nullable'],
                    'search' => ['present', 'string'],
                    'paginate' => ['required', 'boolean'],
                    'page' => ['required_if:paginate,true', 'numeric'],
                    'perPage' => ['required_if:paginate,true', 'numeric'],
                    'refresh' => ['nullable', 'boolean'],
                ];

                return $rules_list;
            case 'read':
                $rules_read = [
                ];

                return $rules_read;
            case 'store':
                $rules_store = [
                    'company_id' => ['required', new isValidCompany(), 'bail'],
                    'code' => ['required', 'max:255'],
                    'name' => ['required', 'min:3', 'max:255'],
                    'product_group_id' => ['required'],
                    'taxable_supply' => ['required', 'boolean'],
                    'use_serial_number' => ['required', 'boolean'],
                    'price_include_vat' => ['required', 'boolean'],
                    'has_expiry_date' => ['required', 'boolean'],
                    'point' => ['required', 'numeric', 'min:0'],
                    'status' => [new Enum(RecordStatus::class)],
                    'product_type' => [new Enum(ProductType::class)],
                    'product_units_code.*' => ['required', 'max:255'],
                    'product_units_unit_id.*' => ['required'],
                    'product_units_conv_value.*' => ['numeric', 'min:1'],
                    'product_units_is_base.*' => ['required', 'boolean'],
                    'product_units_is_primary_unit.*' => ['required', 'boolean'],
                ];

                return array_merge($rules_store, $nullableArr);
            case 'update':
                $rules_update = [
                    'company_id' => ['required', new isValidCompany(), 'bail'],
                    'code' => ['required', 'max:255'],
                    'name' => 'required|min:3|max:255',
                    'product_group_id' => ['required'],
                    'taxable_supply' => ['required', 'boolean'],
                    'use_serial_number' => ['required', 'boolean'],
                    'price_include_vat' => ['required', 'boolean'],
                    'has_expiry_date' => ['required', 'boolean'],
                    'point' => ['required', 'numeric', 'min:0'],
                    'status' => [new Enum(RecordStatus::class)],
                    'product_type' => [new Enum(ProductType::class)],
                    'product_units_code.*' => ['required', 'max:255'],
                    'product_units_unit_id.*' => ['required'],
                    'product_units_conv_value.*' => ['numeric', 'min:1'],
                    'product_units_is_base.*' => ['required', 'boolean'],
                    'product_units_is_primary_unit.*' => ['required', 'boolean'],
                ];

                return array_merge($rules_update, $nullableArr);
            default:
                return [
                    '' => 'required',
                ];
        }
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.company'),
            'product_units_conv_value.*' => trans('validation_attributes.conv_value'),
        ];
    }

    public function validationData()
    {
        $additionalArray = [];

        return array_merge($this->all(), $additionalArray);
    }

    public function prepareForValidation()
    {
        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'list':
                $this->merge([
                    'company_id' => $this->has('companyId') ? Hashids::decode($this['companyId'])[0] : '',
                    'paginate' => $this->has('paginate') ? filter_var($this->paginate, FILTER_VALIDATE_BOOLEAN) : true,
                    'productCategory' => ProductCategory::isValid($this->productCategory) ? ProductCategory::resolveToEnum($this->productCategory)->value : -1,
                ]);
                break;
            case 'read':
                $this->merge([]);
                break;
            case 'store':
            case 'update':
                $product_units_id = [];
                if ($this->has('product_units_hId')) {
                    for ($i = 0; $i < count($this->product_units_hId); $i++) {
                        if ($this->product_units_hId[$i] != '') {
                            array_push($product_units_id, Hashids::decode($this->product_units_hId[$i])[0]);
                        } else {
                            array_push($product_units_id, null);
                        }
                    }
                }

                $product_units_unit_id = [];
                if ($this->has('product_units_unit_hId')) {
                    for ($i = 0; $i < count($this->product_units_unit_hId); $i++) {
                        if ($this->product_units_unit_hId[$i] != '') {
                            array_push($product_units_unit_id, Hashids::decode($this->product_units_unit_hId[$i])[0]);
                        } else {
                            array_push($product_units_unit_id, null);
                        }
                    }
                }

                $product_units_is_base = [];
                if ($this->has('product_units_is_base')) {
                    for ($i = 0; $i < count($this->product_units_is_base); $i++) {
                        $is_base = filter_var($this->product_units_is_base[$i], FILTER_VALIDATE_BOOLEAN) ? true : false;
                        array_push($product_units_is_base, $is_base);
                    }
                }

                $product_units_is_primary_unit = [];
                if ($this->has('product_units_is_primary_unit')) {
                    for ($i = 0; $i < count($this->product_units_is_primary_unit); $i++) {
                        $unit_id = filter_var($this->product_units_is_primary_unit[$i], FILTER_VALIDATE_BOOLEAN) ? true : false;
                        array_push($product_units_is_primary_unit, $unit_id);
                    }
                }

                $this->merge([
                    'company_id' => $this->has('company_id') ? Hashids::decode($this['company_id'])[0] : '',
                    'product_group_id' => $this->has('product_group_id') ? Hashids::decode($this['product_group_id'])[0] : '',
                    'brand_id' => $this->has('brand_id') ? Hashids::decode($this['brand_id'])[0] : null,
                    'taxable_supply' => $this->has('taxable_supply') ? filter_var($this->taxable_supply, FILTER_VALIDATE_BOOLEAN) : false,
                    'use_serial_number' => $this->has('use_serial_number') ? filter_var($this->use_serial_number, FILTER_VALIDATE_BOOLEAN) : false,
                    'price_include_vat' => $this->has('price_include_vat') ? filter_var($this->price_include_vat, FILTER_VALIDATE_BOOLEAN) : false,
                    'has_expiry_date' => $this->has('has_expiry_date') ? filter_var($this->has_expiry_date, FILTER_VALIDATE_BOOLEAN) : false,
                    'product_type' => ProductType::isValid($this->product_type) ? ProductType::resolveToEnum($this->product_type)->value : -1,
                    'status' => RecordStatus::isValid($this->status) ? RecordStatus::resolveToEnum($this->status)->value : -1,
                    'product_units_id' => $product_units_id,
                    'product_units_unit_id' => $product_units_unit_id,
                    'product_units_is_base' => $product_units_is_base,
                    'product_units_is_primary_unit' => $product_units_is_primary_unit,
                ]);
                break;
            default:
                $this->merge([]);
                break;
        }
    }
}
