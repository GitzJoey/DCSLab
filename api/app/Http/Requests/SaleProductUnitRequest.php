<?php

namespace App\Http\Requests;

use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Models\SaleProductUnit;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidProduct;
use App\Rules\IsValidProductUnit;
use App\Rules\IsValidSale;
use App\Rules\IsValidWarehouse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SaleProductUnitRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $saleProductUnit = $this->route('sale_product_unit');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                return $user->can('viewAny', SaleProductUnit::class) ? true : false;
            case 'read':
                return $user->can('view', SaleProductUnit::class, $saleProductUnit) ? true : false;
            case 'store':
                return $user->can('create', SaleProductUnit::class) ? true : false;
            case 'update':
                return $user->can('update', SaleProductUnit::class, $saleProductUnit) ? true : false;
            case 'delete':
                return $user->can('delete', SaleProductUnit::class, $saleProductUnit) ? true : false;
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
        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                return [
                    'refresh' => ['required', 'boolean'],
                    'with_trashed' => ['required', 'boolean'],

                    'search' => ['nullable', 'string'],
                    'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
                    'status' => ['nullable', 'integer', 'in:'.implode(',', RecordStatusEnum::toArrayValue())],

                    'paginate' => ['required', 'boolean'],
                    'page' => ['nullable', 'required_if:paginate,true', 'numeric', 'min:1'],
                    'per_page' => ['nullable', 'required_if:paginate,true', 'numeric', 'min:10'],
                    'limit' => ['nullable', 'integer', 'min:1'],
                ];
            case 'read':
                return [];
            case 'store':
                return [
                    'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
                    'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
                    'sale_id' => ['required', 'integer', new IsValidSale()],
                    'warehouse_id' => ['required', 'integer', new IsValidWarehouse($this->company_id, $this->branch_id)],

                    'qty' => ['required', 'numeric', 'min:1'],
                    'product_id' => ['required', 'integer', new IsValidProduct($this->company_id)],
                    'product_unit_id' => ['required', 'integer', new IsValidProductUnit($this->company_id)],
                    'product_unit_amount_per_unit' => ['required', 'numeric', 'min:0'],
                    'product_unit_amount_total' => ['required', 'numeric', 'min:0'],
                    'product_unit_initial_price' => ['required', 'numeric', 'min:0'],
                    'product_unit_discount_rate1' => ['required', 'numeric', 'min:0'],
                    'product_unit_discount_rate2' => ['required', 'numeric', 'min:0'],
                    'product_unit_discount_rate3' => ['required', 'numeric', 'min:0'],
                    'product_unit_discount_rate4' => ['required', 'numeric', 'min:0'],
                    'product_unit_discount_rate5' => ['required', 'numeric', 'min:0'],
                    'product_unit_discount_fixed1' => ['required', 'numeric', 'min:0'],
                    'product_unit_discount_fixed2' => ['required', 'numeric', 'min:0'],
                    'product_unit_discount_fixed3' => ['required', 'numeric', 'min:0'],
                    'product_unit_discount_fixed4' => ['required', 'numeric', 'min:0'],
                    'product_unit_discount_fixed5' => ['required', 'numeric', 'min:0'],
                    'product_unit_net_price' => ['required', 'numeric', 'min:0'],
                    'product_unit_subtotal' => ['required', 'numeric', 'min:0'],
                    'product_unit_subtotal_discount_rate' => ['required', 'numeric', 'min:0'],
                    'product_unit_subtotal_discount_fixed' => ['required', 'numeric', 'min:0'],
                    'product_unit_total' => ['required', 'numeric', 'min:0'],

                    'product_is_taxable' => ['required', 'boolean'],
                    'product_vat_rate' => ['required', 'numeric', 'min:0'],
                    'product_price_include_vat' => ['required', 'boolean'],
                    'product_vat_base' => ['required', 'numeric', 'min:0'],
                    'product_vat' => ['required', 'numeric', 'min:0'],

                    'product_unit_final_price' => ['required', 'numeric', 'min:0'],

                    'is_received' => ['required', 'boolean'],
                    'is_valid' => ['required', 'boolean'],
                ];
            case 'update':
                return [
                    'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
                    'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
                    'sale_id' => ['required', 'integer', new IsValidSale()],
                    'warehouse_id' => ['required', 'integer', new IsValidWarehouse($this->company_id, $this->branch_id)],

                    'qty' => ['required', 'numeric', 'min:1'],
                    'product_id' => ['required', 'integer', new IsValidProduct($this->company_id)],
                    'product_unit_id' => ['required', 'integer', new IsValidProductUnit($this->company_id)],
                    'product_unit_amount_per_unit' => ['required', 'numeric', 'min:0'],
                    'product_unit_amount_total' => ['required', 'numeric', 'min:0'],
                    'product_unit_initial_price' => ['required', 'numeric', 'min:0'],
                    'product_unit_discount_rate1' => ['required', 'numeric', 'min:0'],
                    'product_unit_discount_rate2' => ['required', 'numeric', 'min:0'],
                    'product_unit_discount_rate3' => ['required', 'numeric', 'min:0'],
                    'product_unit_discount_rate4' => ['required', 'numeric', 'min:0'],
                    'product_unit_discount_rate5' => ['required', 'numeric', 'min:0'],
                    'product_unit_discount_fixed1' => ['required', 'numeric', 'min:0'],
                    'product_unit_discount_fixed2' => ['required', 'numeric', 'min:0'],
                    'product_unit_discount_fixed3' => ['required', 'numeric', 'min:0'],
                    'product_unit_discount_fixed4' => ['required', 'numeric', 'min:0'],
                    'product_unit_discount_fixed5' => ['required', 'numeric', 'min:0'],
                    'product_unit_net_price' => ['required', 'numeric', 'min:0'],
                    'product_unit_subtotal' => ['required', 'numeric', 'min:0'],
                    'product_unit_subtotal_discount_rate' => ['required', 'numeric', 'min:0'],
                    'product_unit_subtotal_discount_fixed' => ['required', 'numeric', 'min:0'],
                    'product_unit_total' => ['required', 'numeric', 'min:0'],

                    'product_is_taxable' => ['required', 'boolean'],
                    'product_vat_rate' => ['required', 'numeric', 'min:0'],
                    'product_price_include_vat' => ['required', 'boolean'],
                    'product_vat_base' => ['required', 'numeric', 'min:0'],
                    'product_vat' => ['required', 'numeric', 'min:0'],

                    'product_unit_final_price' => ['required', 'numeric', 'min:0'],

                    'is_received' => ['required', 'boolean'],
                    'is_valid' => ['required', 'boolean'],
                ];
            case 'delete':
                return [

                ];
            default:
                return [
                    '' => 'required',
                ];
        }
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.sale_product_unit.company'),
            'code' => trans('validation_attributes.sale_product_unit.code'),
            'remarks' => trans('validation_attributes.sale_product_unit.remarks'),
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
            case 'readAny':
                $this->merge([
                    'with_trashed' => $this->has('with_trashed') ? $this->with_trashed : null,

                    'search' => $this->has('search') ? $this->search : null,
                    'company_id' => $this->has('company_id') ? HashidsHelper::decodeId($this->company_id) : null,

                    'page' => $this->has('page') ? $this->page : null,
                    'per_page' => $this->has('per_page') ? $this->per_page : null,
                    'limit' => $this->has('limit') ? $this->limit : null,
                ]);
                break;
            case 'read':
                $this->merge([]);
                break;
            case 'store':
            case 'update':
                $this->merge([
                    'company_id' => $this->has('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
                    'remarks' => $this->has('remarks') ? $this['remarks'] : null,
                ]);
                break;
            default:
                $this->merge([]);
                break;
        }
    }
}
