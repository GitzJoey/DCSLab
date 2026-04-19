<?php

namespace App\Http\Requests\Purchase;

use App\Enums\DiscountTypeEnum;
use App\Helpers\HashidsHelper;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidDate;
use App\Rules\IsValidSupplier;
use App\Rules\IsValidWarehouse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PurchaseStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', \App\Models\Purchase::class);
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'warehouse_id' => $this->filled('warehouse_id') ? HashidsHelper::decodeId($this->warehouse_id) : null,
            'supplier_id' => $this->filled('supplier_id') ? HashidsHelper::decodeId($this->supplier_id) : null,
            'purchase_order_id' => $this->filled('purchase_order_id') ? HashidsHelper::decodeId($this->purchase_order_id) : null,
        ]);

        if (is_array($this->input('items'))) {
            $items = [];
            foreach ($this->input('items') as $item) {
                if (array_key_exists('purchase_order_item_id', $item) && ! is_null($item['purchase_order_item_id'])) {
                    $item['purchase_order_item_id'] = HashidsHelper::decodeId($item['purchase_order_item_id']);
                }
                if (array_key_exists('product_unit_id', $item) && ! is_null($item['product_unit_id'])) {
                    $item['product_unit_id'] = HashidsHelper::decodeId($item['product_unit_id']);
                }
                if (array_key_exists('vat_profile_id', $item) && ! is_null($item['vat_profile_id'])) {
                    $item['vat_profile_id'] = HashidsHelper::decodeId($item['vat_profile_id']);
                }
                $items[] = $item;
            }
            $this->merge(['items' => $items]);
        }
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', 'bail', new IsValidBranch($this->company_id, true)],
            'code' => ['required', 'string'],
            'date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'due_days' => ['required', 'integer', 'min:0'],
            'warehouse_id' => ['present', 'nullable', 'integer', 'bail', new ExistsForCompany('warehouses', $this->company_id), new IsValidWarehouse($this->company_id, false)],
            'supplier_id' => [
                'present',
                'nullable',
                'integer',
                'bail',
                new ExistsForCompany('suppliers', $this->company_id),
                new IsValidSupplier($this->company_id),
            ],
            'purchase_order_id' => ['present', 'nullable', 'integer', new ExistsForCompany('purchase_orders', $this->company_id)],
            'tax_invoice_number' => ['present', 'nullable', 'string'],
            'tax_invoice_vat_base' => ['required', 'numeric', 'min:0'],
            'tax_invoice_vat' => ['required', 'numeric', 'min:0'],
            'remarks' => ['present', 'nullable', 'string'],
            'is_posted' => ['required', 'boolean'],
            'additional_cost' => ['required', 'numeric', 'min:0'],
            'rounding' => ['required', 'numeric'],

            'global_discounts' => ['required', 'array'],
            'global_discounts.*.sequence' => ['required', 'integer', 'min:1'],
            'global_discounts.*.discount_type' => ['required', Rule::enum(DiscountTypeEnum::class)],
            'global_discounts.*.discount_value' => ['required', 'numeric', 'min:0'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_order_item_id' => [
                'present',
                'nullable',
                'integer',
                new ExistsForCompany('purchase_order_items', $this->company_id),
            ],
            'items.*.qty' => ['required', 'numeric', 'gt:0'],
            'items.*.product_unit_id' => ['required', 'integer', new ExistsForCompany('product_units', $this->company_id)],
            'items.*.product_unit_conversion_value' => ['required', 'numeric', 'gt:0'],
            'items.*.product_unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.product_unit_is_price_include_vat' => ['required', 'boolean'],
            'items.*.product_unit_price_discounts' => ['required', 'array'],
            'items.*.product_unit_price_discounts.*.sequence' => ['required', 'integer', 'min:1'],
            'items.*.product_unit_price_discounts.*.discount_type' => ['required', Rule::enum(DiscountTypeEnum::class)],
            'items.*.product_unit_price_discounts.*.discount_value' => ['required', 'numeric', 'min:0'],
            'items.*.subtotal_discounts' => ['required', 'array'],
            'items.*.subtotal_discounts.*.sequence' => ['required', 'integer', 'min:1'],
            'items.*.subtotal_discounts.*.discount_type' => ['required', Rule::enum(DiscountTypeEnum::class)],
            'items.*.subtotal_discounts.*.discount_value' => ['required', 'numeric', 'min:0'],
            'items.*.vat_profile_id' => [
                'present',
                'nullable',
                'integer',
                new ExistsForCompany('vat_profiles', $this->company_id),
            ],
            'items.*.vat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.vat_base_numerator' => ['required', 'integer', 'min:1'],
            'items.*.vat_base_denominator' => ['required', 'integer', 'min:1'],
            'items.*.remarks' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.purchase.company_id'),
            'branch_id' => trans('validation_attributes.purchase.branch_id'),
            'code' => trans('validation_attributes.purchase.code'),
            'date' => trans('validation_attributes.purchase.date'),
            'due_days' => trans('validation_attributes.purchase.due_days'),
            'warehouse_id' => trans('validation_attributes.purchase.warehouse_id'),
            'supplier_id' => trans('validation_attributes.purchase.supplier_id'),
            'purchase_order_id' => trans('validation_attributes.purchase.purchase_order_id'),
            'tax_invoice_number' => trans('validation_attributes.purchase.tax_invoice_number'),
            'tax_invoice_vat_base' => trans('validation_attributes.purchase.tax_invoice_vat_base'),
            'tax_invoice_vat' => trans('validation_attributes.purchase.tax_invoice_vat'),
            'remarks' => trans('validation_attributes.purchase.remarks'),

            'global_discounts.*.sequence' => trans('validation_attributes.purchase_order_global_discount.sequence'),
            'global_discounts.*.discount_type' => trans('validation_attributes.purchase_order_global_discount.discount_type'),
            'global_discounts.*.discount_value' => trans('validation_attributes.purchase_order_global_discount.discount_value'),

            'items.*.qty' => trans('validation_attributes.purchase_order_item.qty'),
            'items.*.product_unit_id' => trans('validation_attributes.purchase_order_item.product_unit_id'),
            'items.*.product_unit_conversion_value' => trans('validation_attributes.purchase_order_item.product_unit_conversion_value'),
            'items.*.product_unit_price' => trans('validation_attributes.purchase_order_item.product_unit_price'),
            'items.*.product_unit_is_price_include_vat' => trans('validation_attributes.purchase_order_item.product_unit_is_price_include_vat'),
            'items.*.vat_profile_id' => trans('validation_attributes.purchase_order_item.vat_profile_id'),
            'items.*.vat_rate' => trans('validation_attributes.purchase_order_item.vat_rate'),
            'items.*.vat_base_numerator' => trans('validation_attributes.purchase_order_item.vat_base_numerator'),
            'items.*.vat_base_denominator' => trans('validation_attributes.purchase_order_item.vat_base_denominator'),
            'items.*.remarks' => trans('validation_attributes.purchase_order_item.remarks'),
        ];
    }
}
