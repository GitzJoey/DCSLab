<?php

namespace App\Http\Requests\PurchaseProductUnit;

use App\Helpers\HashidsHelper;
use App\Models\PurchaseProductUnit;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidProduct;
use App\Rules\IsValidProductUnit;
use App\Rules\IsValidPurchase;
use App\Rules\IsValidWarehouse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseProductUnitStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', PurchaseProductUnit::class);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'purchase_id' => ['required', 'integer', new IsValidPurchase()],
            'warehouse_id' => ['nullable', 'integer', new IsValidWarehouse($this->company_id, false)],
            'qty' => ['required', 'numeric', 'min:1'],
            'product_id' => ['required', 'integer', new IsValidProduct($this->company_id)],
            'product_unit_id' => ['required', 'integer', new IsValidProductUnit()],
            'product_unit_amount_per_unit' => ['required', 'numeric', 'min:1'],
            'product_unit_amount_total' => ['required', 'numeric', 'min:1'],
            'product_unit_initial_price' => ['required', 'numeric', 'min:1'],
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
            'product_unit_net_price' => ['required', 'numeric', 'min:1'],
            'product_unit_subtotal' => ['required', 'numeric', 'min:1'],
            'product_unit_subtotal_discount_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'product_unit_subtotal_discount_fixed' => ['required', 'numeric', 'min:0'],
            'product_unit_total' => ['required', 'numeric', 'min:1'],
            'product_unit_global_discount_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'product_unit_global_discount_fixed' => ['required', 'numeric', 'min:0'],
            'product_unit_grand_total' => ['required', 'numeric', 'min:1'],
            'product_is_taxable' => ['required', 'boolean'],
            'product_vat_rate' => ['required', 'numeric', 'min:0'],
            'product_price_includes_vat' => ['required', 'boolean'],
            'product_vat_base' => ['required', 'numeric', 'min:0'],
            'product_vat' => ['required', 'numeric', 'min:0'],
            'product_base_unit_final_price' => ['required', 'numeric', 'min:1'],
            'is_received' => ['required', 'boolean'],
            'is_valid' => ['required', 'boolean'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.purchase_product_unit.company'),
            'branch_id' => trans('validation_attributes.purchase_product_unit.branch'),
            'purchase_id' => trans('validation_attributes.purchase_product_unit.purchase'),
            'warehouse_id' => trans('validation_attributes.purchase_product_unit.warehouse'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
        ]);
    }
}
