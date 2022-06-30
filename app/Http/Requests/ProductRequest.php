<?php

namespace App\Http\Requests;

use App\Enums\RecordStatus;
use App\Enums\ProductType;
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
        if (!Auth::check()) return false;

        /** @var \App\User */
        $user = Auth::user();
        $product = $this->route('product');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch($currentRouteMethod) {
            case 'listProducts':
            case 'listServices':
                return $user->can('viewAny', Product::class) ? true : false;
            case 'readProducts':
            case 'readServices':
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
            case 'listProducts':
            case 'listServices':
                $rules_list = [
                    'company_id' => ['required', new isValidCompany(), 'bail'],
                    'search' => ['present', 'string'],
                    'paginate' => ['required', 'boolean'],
                    'page' => ['required_if:paginate,true', 'numeric'],
                    'perPage' => ['required_if:paginate,true', 'numeric'],
                    'refresh' => ['nullable', 'boolean']
                ];
                return $rules_list;
            case 'readProducts':
            case 'readServices':
                $rules_read = [
                ];
                return $rules_read;
            case 'store':
                $rules_store = [
                    'company_id' => ['required', new isValidCompany(), 'bail'],
                    'code' => ['required', 'max:255'],
                    'name' => 'required|min:3|max:255',
                    'brand_id' => 'required',
                    'taxable_supply' => 'required|boolean',
                    'use_serial_number' => 'required|boolean',
                    'price_include_vat' => 'required|boolean',
                    'has_expiry_date' => 'required|boolean',
                    'point' => 'required|numeric|min:0',
                    'status' => [new Enum(RecordStatus::class)],
                    'product_type' => [new Enum(ProductType::class)],
                    'conv_value.*' => 'numeric|min:1',
                ];
                return array_merge($rules_store, $nullableArr);
            case 'update':
                $rules_update = [
                    'company_id' => ['required', new isValidCompany(), 'bail'],
                    'code' => ['required', 'max:255'],
                    'name' => 'required|min:3|max:255',
                    'brand_id' => 'required',
                    'taxable_supply' => 'required|boolean',
                    'use_serial_number' => 'required|boolean',
                    'price_include_vat' => 'required|boolean',
                    'has_expiry_date' => 'required|boolean',
                    'point' => 'required|numeric|min:0',
                    'status' => [new Enum(RecordStatus::class)],
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
        $currentRouteMethod = $this->route()->getActionMethod();
        switch($currentRouteMethod) {
            case 'listProducts':
            case 'listServices':
                $this->merge([
                    'company_id' => $this->has('companyId') ? Hashids::decode($this['companyId'])[0]:'',
                    'paginate' => $this->has('paginate') ? filter_var($this->paginate, FILTER_VALIDATE_BOOLEAN) : true,
                ]);
                break;
            case 'readProducts':
            case 'readServices':
                $this->merge([]);
                break;
            case 'store':
            case 'update':
                $this->merge([
                    'company_id' => $this->has('company_id') ? Hashids::decode($this['company_id'])[0]:'',
                    'taxable_supply' => $this->has('taxable_supply') ? filter_var($this->taxable_supply, FILTER_VALIDATE_BOOLEAN) : false,
                    'use_serial_number' => $this->has('use_serial_number') ? filter_var($this->use_serial_number, FILTER_VALIDATE_BOOLEAN) : false,
                    'price_include_vat' => $this->has('price_include_vat') ? filter_var($this->price_include_vat, FILTER_VALIDATE_BOOLEAN) : false,
                    'has_expiry_date' => $this->has('has_expiry_date') ? filter_var($this->has_expiry_date, FILTER_VALIDATE_BOOLEAN) : false,
                    'product_type' => ProductType::isValid($this->product_type) ? ProductType::fromName($this->product_type)->value : -1,
                    'status' => RecordStatus::isValid($this->status) ? RecordStatus::resolveToEnum($this->status)->value : -1
                ]);
                break;
            default:
                $this->merge([]);
                break;
        }
    }
}
