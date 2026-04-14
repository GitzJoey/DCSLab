<?php

namespace App\Http\Requests\PurchaseOrder;

use App\Enums\DiscountTypeEnum;
use App\Helpers\HashidsHelper;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidCompany;
use App\Rules\IsValidDate;
use App\Rules\IsValidSupplier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PurchaseOrderUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $purchaseOrder = $this->route('purchase_order');

        return $user->can('update', $purchaseOrder);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', 'bail', new IsValidBranch($this->company_id, true)],
            'code' => ['required', 'string', 'max:255'],
            'date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'due_days' => ['required', 'integer', 'min:0'],
            'supplier_id' => ['present', 'nullable', 'integer', new IsValidSupplier($this->company_id)],
            'remarks' => ['present', 'nullable', 'string'],
            'rounding' => ['required', 'numeric'],

            'global_discounts' => ['present', 'array'],
            'global_discounts.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('purchase_order_global_discounts', $this->company_id)],
            'global_discounts.*.sequence' => ['required', 'integer', 'min:1'],
            'global_discounts.*.discount_type' => ['required', Rule::enum(DiscountTypeEnum::class)],
            'global_discounts.*.discount_value' => ['required', 'numeric', 'min:0'],
            'delete_global_discount_ids' => ['present', 'array'],
            'delete_global_discount_ids.*' => ['required', 'integer', new ExistsForCompany('purchase_order_global_discounts', $this->company_id)],

            'delete_item_ids' => ['present', 'array'],
            'delete_item_ids.*' => ['required', 'integer', new ExistsForCompany('purchase_order_items', $this->company_id)],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('purchase_order_items', $this->company_id)],
            'items.*.qty' => ['required', 'numeric', 'min:0.00000001'],
            'items.*.product_unit_id' => ['required', 'integer', new ExistsForCompany('product_units', $this->company_id)],
            'items.*.product_unit_conversion_value' => ['required', 'numeric', 'min:1'],
            'items.*.product_unit_price' => ['required', 'numeric', 'min:0'],

            'items.*.delete_product_unit_price_discount_ids' => ['present', 'array'],
            'items.*.delete_product_unit_price_discount_ids.*' => ['required', 'integer', new ExistsForCompany('purchase_order_item_product_unit_price_discounts', $this->company_id)],
            'items.*.product_unit_price_discounts' => ['present', 'array'],
            'items.*.product_unit_price_discounts.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('purchase_order_item_product_unit_price_discounts', $this->company_id)],
            'items.*.product_unit_price_discounts.*.sequence' => ['required', 'integer', 'min:1'],
            'items.*.product_unit_price_discounts.*.discount_type' => ['required', Rule::enum(DiscountTypeEnum::class)],
            'items.*.product_unit_price_discounts.*.discount_value' => ['required', 'numeric', 'min:0'],

            'items.*.delete_subtotal_discount_ids' => ['present', 'array'],
            'items.*.delete_subtotal_discount_ids.*' => ['required', 'integer', new ExistsForCompany('purchase_order_item_subtotal_discounts', $this->company_id)],
            'items.*.subtotal_discounts' => ['present', 'array'],
            'items.*.subtotal_discounts.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('purchase_order_item_subtotal_discounts', $this->company_id)],
            'items.*.subtotal_discounts.*.sequence' => ['required', 'integer', 'min:1'],
            'items.*.subtotal_discounts.*.discount_type' => ['required', Rule::enum(DiscountTypeEnum::class)],
            'items.*.subtotal_discounts.*.discount_value' => ['required', 'numeric', 'min:0'],

            'items.*.is_vat_included' => ['required', 'boolean'],
            'items.*.vat_profile_id' => ['present', 'nullable', 'integer', new ExistsForCompany('vat_profiles', $this->company_id)],
            'items.*.vat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.vat_base_numerator' => ['required', 'integer', 'min:1'],
            'items.*.vat_base_denominator' => ['required', 'integer', 'min:1'],
            'items.*.remarks' => ['present', 'nullable', 'string', 'max:255'],

            'delete_down_payment_ids' => ['present', 'array'],
            'delete_down_payment_ids.*' => ['required', 'integer', new ExistsForCompany('purchase_order_down_payments', $this->company_id)],
            'down_payments' => ['present', 'array'],
            'down_payments.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('purchase_order_down_payments', $this->company_id)],
            'down_payments.*.code' => ['required', 'string', 'max:255'],
            'down_payments.*.date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'down_payments.*.cash_account_id' => ['required', 'integer', 'bail', new ExistsForCompany('cash_accounts', $this->company_id), new IsValidCashAccount($this->branch_id)],
            'down_payments.*.amount' => ['required', 'numeric', 'min:0'],
            'down_payments.*.remarks' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.purchase_order.company_id'),
            'branch_id' => trans('validation_attributes.purchase_order.branch_id'),
            'code' => trans('validation_attributes.purchase_order.code'),
            'date' => trans('validation_attributes.purchase_order.date'),
            'due_days' => trans('validation_attributes.purchase_order.due_days'),
            'supplier_id' => trans('validation_attributes.purchase_order.supplier_id'),
            'remarks' => trans('validation_attributes.purchase_order.remarks'),
            'rounding' => trans('validation_attributes.purchase_order.rounding'),

            'global_discounts.*.sequence' => trans('validation_attributes.purchase_order_global_discount.sequence'),
            'global_discounts.*.discount_type' => trans('validation_attributes.purchase_order_global_discount.discount_type'),
            'global_discounts.*.discount_value' => trans('validation_attributes.purchase_order_global_discount.discount_value'),

            'items.*.qty' => trans('validation_attributes.purchase_order_item.qty'),
            'items.*.product_unit_id' => trans('validation_attributes.purchase_order_item.product_unit_id'),
            'items.*.product_unit_conversion_value' => trans('validation_attributes.purchase_order_item.product_unit_conversion_value'),
            'items.*.product_unit_price' => trans('validation_attributes.purchase_order_item.product_unit_price'),
            'items.*.is_vat_included' => trans('validation_attributes.purchase_order_item.is_vat_included'),
            'items.*.vat_profile_id' => trans('validation_attributes.purchase_order_item.vat_profile_id'),
            'items.*.vat_rate' => trans('validation_attributes.purchase_order_item.vat_rate'),
            'items.*.vat_base_numerator' => trans('validation_attributes.purchase_order_item.vat_base_numerator'),
            'items.*.vat_base_denominator' => trans('validation_attributes.purchase_order_item.vat_base_denominator'),
            'items.*.remarks' => trans('validation_attributes.purchase_order_item.remarks'),

            'down_payments.*.code' => trans('validation_attributes.purchase_order_down_payment.code'),
            'down_payments.*.date' => trans('validation_attributes.purchase_order_down_payment.date'),
            'down_payments.*.cash_account_id' => trans('validation_attributes.purchase_order_down_payment.cash_account_id'),
            'down_payments.*.amount' => trans('validation_attributes.purchase_order_down_payment.amount'),
            'down_payments.*.remarks' => trans('validation_attributes.purchase_order_down_payment.remarks'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'supplier_id' => $this->filled('supplier_id') ? HashidsHelper::decodeId($this->supplier_id) : null,
        ]);

        if (is_array($this->input('global_discounts'))) {
            $globalDiscounts = [];
            foreach ($this->input('global_discounts') as $globalDiscount) {
                if (array_key_exists('id', $globalDiscount) && ! is_null($globalDiscount['id'])) {
                    $globalDiscount['id'] = HashidsHelper::decodeId($globalDiscount['id']);
                }
                $globalDiscounts[] = $globalDiscount;
            }
            $this->merge(['global_discounts' => $globalDiscounts]);
        }

        if (is_array($this->input('delete_global_discount_ids'))) {
            $deleteGlobalDiscountIds = [];
            foreach ($this->input('delete_global_discount_ids') as $id) {
                $deleteGlobalDiscountIds[] = HashidsHelper::decodeId($id);
            }
            $this->merge(['delete_global_discount_ids' => $deleteGlobalDiscountIds]);
        }

        if (is_array($this->input('items'))) {
            $items = [];
            foreach ($this->input('items') as $item) {
                if (array_key_exists('id', $item) && ! is_null($item['id'])) {
                    $item['id'] = HashidsHelper::decodeId($item['id']);
                }
                if (array_key_exists('product_unit_id', $item) && ! is_null($item['product_unit_id'])) {
                    $item['product_unit_id'] = HashidsHelper::decodeId($item['product_unit_id']);
                }
                if (array_key_exists('vat_profile_id', $item) && ! is_null($item['vat_profile_id'])) {
                    $item['vat_profile_id'] = HashidsHelper::decodeId($item['vat_profile_id']);
                }
                if (isset($item['product_unit_price_discounts']) && is_array($item['product_unit_price_discounts'])) {
                    $priceDiscounts = [];
                    foreach ($item['product_unit_price_discounts'] as $discount) {
                        if (array_key_exists('id', $discount) && ! is_null($discount['id'])) {
                            $discount['id'] = HashidsHelper::decodeId($discount['id']);
                        }
                        $priceDiscounts[] = $discount;
                    }
                    $item['product_unit_price_discounts'] = $priceDiscounts;
                }
                if (isset($item['subtotal_discounts']) && is_array($item['subtotal_discounts'])) {
                    $subtotalDiscounts = [];
                    foreach ($item['subtotal_discounts'] as $discount) {
                        if (array_key_exists('id', $discount) && ! is_null($discount['id'])) {
                            $discount['id'] = HashidsHelper::decodeId($discount['id']);
                        }
                        $subtotalDiscounts[] = $discount;
                    }
                    $item['subtotal_discounts'] = $subtotalDiscounts;
                }
                if (isset($item['delete_product_unit_price_discount_ids']) && is_array($item['delete_product_unit_price_discount_ids'])) {
                    $deletePriceDiscountIds = [];
                    foreach ($item['delete_product_unit_price_discount_ids'] as $id) {
                        $deletePriceDiscountIds[] = HashidsHelper::decodeId($id);
                    }
                    $item['delete_product_unit_price_discount_ids'] = $deletePriceDiscountIds;
                }
                if (isset($item['delete_subtotal_discount_ids']) && is_array($item['delete_subtotal_discount_ids'])) {
                    $deleteSubtotalDiscountIds = [];
                    foreach ($item['delete_subtotal_discount_ids'] as $id) {
                        $deleteSubtotalDiscountIds[] = HashidsHelper::decodeId($id);
                    }
                    $item['delete_subtotal_discount_ids'] = $deleteSubtotalDiscountIds;
                }
                $items[] = $item;
            }
            $this->merge(['items' => $items]);
        }

        if (is_array($this->input('delete_item_ids'))) {
            $deleteItemIds = [];
            foreach ($this->input('delete_item_ids') as $id) {
                $deleteItemIds[] = HashidsHelper::decodeId($id);
            }
            $this->merge(['delete_item_ids' => $deleteItemIds]);
        }

        if (is_array($this->input('down_payments'))) {
            $downPayments = [];
            foreach ($this->input('down_payments') as $downPayment) {
                if (array_key_exists('id', $downPayment) && ! is_null($downPayment['id'])) {
                    $downPayment['id'] = HashidsHelper::decodeId($downPayment['id']);
                }
                if (array_key_exists('cash_account_id', $downPayment) && ! is_null($downPayment['cash_account_id'])) {
                    $downPayment['cash_account_id'] = HashidsHelper::decodeId($downPayment['cash_account_id']);
                }
                $downPayments[] = $downPayment;
            }
            $this->merge(['down_payments' => $downPayments]);
        }

        if (is_array($this->input('delete_down_payment_ids'))) {
            $deleteDownPaymentIds = [];
            foreach ($this->input('delete_down_payment_ids') as $id) {
                $deleteDownPaymentIds[] = HashidsHelper::decodeId($id);
            }
            $this->merge(['delete_down_payment_ids' => $deleteDownPaymentIds]);
        }
    }
}
