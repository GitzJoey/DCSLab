<?php

namespace App\Http\Requests\PurchaseOrderProductUnit;

use App\Helpers\HashidsHelper;
use App\Models\PurchaseOrderProductUnit;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidProduct;
use App\Rules\IsValidProductUnit;
use App\Rules\IsValidPurchaseOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderProductUnitUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $purchaseOrderProductUnit = $this->route('purchase_order_product_unit');

        return $user->can('update', PurchaseOrderProductUnit::class, $purchaseOrderProductUnit) ? true : false;
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'purchase_order_id' => ['required', 'integer', 'bail', new IsValidPurchaseOrder()],
            'qty' => ['required', 'numeric', 'min:1'],
            'product_id' => ['required', 'integer', 'bail', new IsValidProduct($this->company_id)],
            'product_unit_id' => ['required', 'integer', 'bail', new IsValidProductUnit($this->company_id, $this->product_id)],
            'product_unit_amount_per_unit' => ['required', 'numeric', 'min:0'],
            'product_unit_amount_total' => ['required', 'numeric', 'min:0'],
            'product_unit_initial_price' => ['required', 'numeric', 'min:0'],
            'discounts' => ['present', 'array'],
            'discounts.*.sequence' => ['required', 'integer', 'min:1', 'distinct'],
            'discounts.*.rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'discounts.*.fixed' => ['required', 'numeric', 'min:0'],
            'product_unit_net_price' => ['required', 'numeric', 'min:0'],
            'product_unit_subtotal' => ['required', 'numeric', 'min:0'],
            'product_unit_subtotal_discount_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'product_unit_subtotal_discount_fixed' => ['required', 'numeric', 'min:0'],
            'product_unit_total' => ['required', 'numeric', 'min:0'],
            'product_unit_global_discount_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'product_unit_global_discount_fixed' => ['required', 'numeric', 'min:0'],
            'product_unit_grand_total' => ['required', 'numeric', 'min:0'],
            'product_is_taxable' => ['required', 'boolean'],
            'product_vat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'product_price_include_vat' => ['required', 'boolean'],
            'product_vat_base' => ['required', 'numeric', 'min:0'],
            'product_vat' => ['required', 'numeric', 'min:0'],
            'product_unit_final_price' => ['required', 'numeric', 'min:0'],
            'product_final_price_base_unit' => ['required', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.purchase_order_product_unit.company_id'),
            'branch_id' => trans('validation_attributes.purchase_order_product_unit.branch_id'),
            'purchase_order_id' => trans('validation_attributes.purchase_order_product_unit.purchase_order_id'),
            'product_id' => trans('validation_attributes.purchase_order_product_unit.product_id'),
            'product_unit_id' => trans('validation_attributes.purchase_order_product_unit.product_unit_id'),
            'discounts' => trans('validation_attributes.purchase_order_product_unit.discounts'),
            'discounts.*.sequence' => trans('validation_attributes.purchase_order_product_unit.discount_sequence'),
            'discounts.*.rate' => trans('validation_attributes.purchase_order_product_unit.discount_rate'),
            'discounts.*.fixed' => trans('validation_attributes.purchase_order_product_unit.discount_fixed'),
            'remarks' => trans('validation_attributes.purchase_order_product_unit.remarks'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'purchase_order_id' => $this->filled('purchase_order_id') ? HashidsHelper::decodeId($this->purchase_order_id) : null,
            'product_id' => $this->filled('product_id') ? HashidsHelper::decodeId($this->product_id) : null,
            'product_unit_id' => $this->filled('product_unit_id') ? HashidsHelper::decodeId($this->product_unit_id) : null,
            'discounts' => $this->normalizeDiscounts(),
            'remarks' => $this->filled('remarks') ? $this->remarks : null,
        ]);
    }

    private function normalizeDiscounts(): array
    {
        if ($this->has('discounts') && is_array($this->discounts)) {
            return collect($this->discounts)
                ->values()
                ->map(function ($discount, $index) {
                    return [
                        'sequence' => data_get($discount, 'sequence', $index + 1),
                        'rate' => data_get($discount, 'rate', 0),
                        'fixed' => data_get($discount, 'fixed', 0),
                    ];
                })
                ->all();
        }

        return collect(range(1, 5))
            ->map(function ($sequence) {
                return [
                    'sequence' => $sequence,
                    'rate' => $this->input('product_unit_discount_rate'.$sequence, 0),
                    'fixed' => $this->input('product_unit_discount_fixed'.$sequence, 0),
                ];
            })
            ->filter(function ($discount) {
                return (float) $discount['rate'] > 0 || (float) $discount['fixed'] > 0;
            })
            ->values()
            ->all();
    }
}
