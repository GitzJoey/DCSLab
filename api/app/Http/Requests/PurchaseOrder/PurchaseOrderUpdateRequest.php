<?php

namespace App\Http\Requests\PurchaseOrder;

use App\Helpers\HashidsHelper;
use App\Models\PurchaseOrder;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidSupplier;
use App\Rules\PurchaseOrderUpdateValidCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

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

        return $user->can('update', PurchaseOrder::class, $purchaseOrder) ? true : false;
    }

    public function rules()
    {
        return array_merge([
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'supplier_id' => ['required', 'integer', 'bail', new IsValidSupplier($this->company_id)],
            'code' => ['required', 'string', 'max:255', new PurchaseOrderUpdateValidCode($this->company_id, $this->route('purchase_order'))],
            'date' => ['required', 'date'],
            'shipping_date' => ['nullable', 'date'],
            'shipping_address' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:255'],
            'is_has_invoice' => ['required', 'boolean'],
            'is_received' => ['required', 'boolean'],
            'total' => ['required', 'numeric', 'min:0'],
            'global_discount_rate' => ['required', 'numeric', 'min:0'],
            'global_discount_fixed' => ['required', 'numeric', 'min:0'],
            'grand_total' => ['required', 'numeric', 'min:0'],
            'down_payment' => ['required', 'numeric', 'min:0'],
            'down_payment_due_days' => ['required', 'integer', 'min:0'],
            'down_payment_applied' => ['required', 'numeric', 'min:0'],
            'down_payment_remaining' => ['required', 'numeric', 'min:0'],
            'is_down_payment_paid_off' => ['required', 'boolean'],
            'delete_purchase_order_product_unit_ids' => ['present', 'array'],
            'delete_purchase_order_product_unit_ids.*' => ['required', 'integer', 'distinct', new ExistsForCompany('purchase_order_product_units', $this->company_id)],
            'delete_purchase_order_down_payment_ids' => ['present', 'array'],
            'delete_purchase_order_down_payment_ids.*' => ['required', 'integer', 'distinct', new ExistsForCompany('purchase_order_down_payments', $this->company_id)],
        ], $this->purchaseOrderProductUnitRules(), $this->purchaseOrderDownPaymentRules());
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.purchase_order.company'),
            'branch_id' => trans('validation_attributes.purchase_order.branch'),
            'supplier_id' => trans('validation_attributes.purchase_order.supplier'),
            'code' => trans('validation_attributes.purchase_order.code'),
            'date' => trans('validation_attributes.purchase_order.date'),
            'shipping_date' => trans('validation_attributes.purchase_order.shipping_date'),
            'shipping_address' => trans('validation_attributes.purchase_order.shipping_address'),
            'remarks' => trans('validation_attributes.purchase_order.remarks'),
            'total' => trans('validation_attributes.purchase_order.total'),
            'down_payment' => trans('validation_attributes.purchase_order.down_payment'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'supplier_id' => $this->filled('supplier_id') ? HashidsHelper::decodeId($this->supplier_id) : null,
            'shipping_date' => $this->filled('shipping_date') ? $this->shipping_date : null,
            'shipping_address' => $this->filled('shipping_address') ? $this->shipping_address : null,
            'remarks' => $this->filled('remarks') ? $this->remarks : null,
            'purchase_order_product_units' => $this->normalizePurchaseOrderProductUnits(),
            'purchase_order_down_payments' => $this->normalizePurchaseOrderDownPayments(),
            'delete_purchase_order_product_unit_ids' => $this->normalizeDeleteIds('delete_purchase_order_product_unit_ids'),
            'delete_purchase_order_down_payment_ids' => $this->normalizeDeleteIds('delete_purchase_order_down_payment_ids'),
        ]);
    }

    private function purchaseOrderProductUnitRules(): array
    {
        return [
            'purchase_order_product_units' => ['present', 'array'],
            'purchase_order_product_units.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('purchase_order_product_units', $this->company_id)],
            'purchase_order_product_units.*.qty' => ['required', 'numeric', 'min:1'],
            'purchase_order_product_units.*.product_id' => ['required', 'integer', new ExistsForCompany('products', $this->company_id)],
            'purchase_order_product_units.*.product_unit_id' => ['required', 'integer', new ExistsForCompany('product_units', $this->company_id)],
            'purchase_order_product_units.*.product_unit_amount_per_unit' => ['required', 'numeric', 'min:0'],
            'purchase_order_product_units.*.product_unit_amount_total' => ['required', 'numeric', 'min:0'],
            'purchase_order_product_units.*.product_unit_initial_price' => ['required', 'numeric', 'min:0'],
            'purchase_order_product_units.*.discounts' => ['present', 'array'],
            'purchase_order_product_units.*.discounts.*.sequence' => ['required', 'integer', 'min:1', 'distinct'],
            'purchase_order_product_units.*.discounts.*.rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'purchase_order_product_units.*.discounts.*.fixed' => ['required', 'numeric', 'min:0'],
            'purchase_order_product_units.*.product_unit_net_price' => ['required', 'numeric', 'min:0'],
            'purchase_order_product_units.*.product_unit_subtotal' => ['required', 'numeric', 'min:0'],
            'purchase_order_product_units.*.product_unit_subtotal_discount_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'purchase_order_product_units.*.product_unit_subtotal_discount_fixed' => ['required', 'numeric', 'min:0'],
            'purchase_order_product_units.*.product_unit_total' => ['required', 'numeric', 'min:0'],
            'purchase_order_product_units.*.product_unit_global_discount_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'purchase_order_product_units.*.product_unit_global_discount_fixed' => ['required', 'numeric', 'min:0'],
            'purchase_order_product_units.*.product_unit_grand_total' => ['required', 'numeric', 'min:0'],
            'purchase_order_product_units.*.product_is_taxable' => ['required', 'boolean'],
            'purchase_order_product_units.*.product_vat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'purchase_order_product_units.*.product_price_include_vat' => ['required', 'boolean'],
            'purchase_order_product_units.*.product_vat_base' => ['required', 'numeric', 'min:0'],
            'purchase_order_product_units.*.product_vat' => ['required', 'numeric', 'min:0'],
            'purchase_order_product_units.*.product_unit_final_price' => ['required', 'numeric', 'min:0'],
            'purchase_order_product_units.*.product_final_price_base_unit' => ['required', 'numeric', 'min:0'],
            'purchase_order_product_units.*.remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function purchaseOrderDownPaymentRules(): array
    {
        return [
            'purchase_order_down_payments' => ['present', 'array'],
            'purchase_order_down_payments.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('purchase_order_down_payments', $this->company_id)],
            'purchase_order_down_payments.*.code' => ['required', 'string', 'max:255'],
            'purchase_order_down_payments.*.date' => ['required', 'date'],
            'purchase_order_down_payments.*.cash_account_id' => ['required', 'integer', new ExistsForCompany('cash_accounts', $this->company_id)],
            'purchase_order_down_payments.*.amount' => ['required', 'numeric', 'min:0'],
            'purchase_order_down_payments.*.remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function normalizeDeleteIds(string $key): array
    {
        if (! is_array($this->input($key))) {
            return [];
        }

        return collect($this->input($key))
            ->map(fn ($id) => HashidsHelper::decodeId($id))
            ->filter()
            ->values()
            ->all();
    }

    private function normalizePurchaseOrderProductUnits(): array
    {
        if (! is_array($this->input('purchase_order_product_units'))) {
            return [];
        }

        return collect($this->input('purchase_order_product_units'))
            ->map(function ($item) {
                if (! is_array($item)) {
                    return [];
                }

                $item['id'] = ! empty($item['id']) ? HashidsHelper::decodeId($item['id']) : null;
                $item['product_id'] = ! empty($item['product_id']) ? HashidsHelper::decodeId($item['product_id']) : null;
                $item['product_unit_id'] = ! empty($item['product_unit_id']) ? HashidsHelper::decodeId($item['product_unit_id']) : null;
                $item['discounts'] = $this->normalizeDiscounts($item);
                $item['remarks'] = array_key_exists('remarks', $item) && filled($item['remarks']) ? $item['remarks'] : null;

                return $item;
            })
            ->values()
            ->all();
    }

    private function normalizePurchaseOrderDownPayments(): array
    {
        if (! is_array($this->input('purchase_order_down_payments'))) {
            return [];
        }

        return collect($this->input('purchase_order_down_payments'))
            ->map(function ($item) {
                if (! is_array($item)) {
                    return [];
                }

                $item['id'] = ! empty($item['id']) ? HashidsHelper::decodeId($item['id']) : null;
                $item['cash_account_id'] = ! empty($item['cash_account_id']) ? HashidsHelper::decodeId($item['cash_account_id']) : null;
                $item['remarks'] = array_key_exists('remarks', $item) && filled($item['remarks']) ? $item['remarks'] : null;

                return $item;
            })
            ->values()
            ->all();
    }

    private function normalizeDiscounts(array $item): array
    {
        if (isset($item['discounts']) && is_array($item['discounts'])) {
            return collect($item['discounts'])
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
            ->map(function ($sequence) use ($item) {
                return [
                    'sequence' => $sequence,
                    'rate' => data_get($item, 'product_unit_discount_rate'.$sequence, 0),
                    'fixed' => data_get($item, 'product_unit_discount_fixed'.$sequence, 0),
                ];
            })
            ->filter(function ($discount) {
                return (float) $discount['rate'] > 0 || (float) $discount['fixed'] > 0;
            })
            ->values()
            ->all();
    }
}
