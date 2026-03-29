<?php

namespace App\Http\Requests\SaleProductUnit;

use App\Helpers\HashidsHelper;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidProduct;
use App\Rules\IsValidProductUnit;
use App\Rules\IsValidSale;
use App\Rules\IsValidWarehouse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SaleProductUnitUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $saleProductUnit = $this->route('sale_product_unit');

        return $user->can('update', $saleProductUnit);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'sale_id' => ['required', 'integer', new IsValidSale()],
            'warehouse_id' => ['required', 'integer', new IsValidWarehouse($this->company_id, true)],
            'qty' => ['required', 'numeric', 'min:1'],
            'product_id' => ['required', 'integer', new IsValidProduct($this->company_id)],
            'product_unit_id' => ['required', 'integer', new IsValidProductUnit()],
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
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.sale_product_unit.company'),
            'branch_id' => trans('validation_attributes.sale_product_unit.branch'),
            'sale_id' => trans('validation_attributes.sale_product_unit.sale'),
            'warehouse_id' => trans('validation_attributes.sale_product_unit.warehouse'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
        ]);
    }
}
