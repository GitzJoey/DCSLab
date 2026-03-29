<?php

namespace App\Http\Requests\SaleOrderProductUnit;

use App\Helpers\HashidsHelper;
use App\Models\SaleOrderProductUnit;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidProduct;
use App\Rules\IsValidProductUnit;
use App\Rules\IsValidSaleOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SaleOrderProductUnitStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', SaleOrderProductUnit::class);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'sales_order_id' => ['required', 'integer', 'bail', new IsValidSaleOrder()],
            'qty' => ['required', 'numeric', 'min:1'],
            'product_id' => ['required', 'integer', 'bail', new IsValidProduct($this->company_id, true)],
            'product_unit_id' => ['required', 'integer', 'bail', new IsValidProductUnit()],
            'product_unit_amount_per_unit' => ['required', 'numeric', 'min:0'],
            'product_unit_amount_total' => ['required', 'numeric', 'min:0'],
            'product_unit_initial_price' => ['required', 'numeric', 'min:0'],
            'product_unit_discount_rate1' => ['required', 'numeric', 'min:0', 'max:100'],
            'product_unit_discount_rate2' => ['required', 'numeric', 'min:0', 'max:100'],
            'product_unit_discount_rate3' => ['required', 'numeric', 'min:0', 'max:100'],
            'product_unit_discount_rate4' => ['required', 'numeric', 'min:0', 'max:100'],
            'product_unit_discount_rate5' => ['required', 'numeric', 'min:0', 'max:100'],
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
            'product_unit_global_discount_rate' => ['required', 'numeric', 'min:0'],
            'product_unit_global_discount_fixed' => ['required', 'numeric', 'min:0'],
            'product_unit_grand_total' => ['required', 'numeric', 'min:0'],
            'product_is_taxable' => ['required', 'boolean'],
            'product_vat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'product_price_include_vat' => ['required', 'boolean'],
            'product_vat_base' => ['required', 'numeric', 'min:0'],
            'product_vat' => ['required', 'numeric', 'min:0'],
            'product_unit_final_price' => ['required', 'numeric', 'min:0'],
            'product_final_price_base_unit' => ['required', 'numeric', 'min:0'],
            'it_has_sale' => ['required', 'numeric', 'min:0'],
            'it_sent' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.sale_order_product_unit.company'),
            'code' => trans('validation_attributes.sale_order_product_unit.code'),
            'remarks' => trans('validation_attributes.sale_order_product_unit.remarks'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'remarks' => $this->filled('remarks') ? $this['remarks'] : null,
        ]);
    }
}
