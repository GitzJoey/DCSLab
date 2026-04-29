<?php

namespace App\Http\Requests\Purchase;

use App\Enums\DiscountTypeEnum;
use App\Helpers\HashidsHelper;
use App\Models\ProductUnit;
use App\Models\Purchase;
use App\Models\PurchaseAdditionalCost;
use App\Models\PurchaseOrder;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidCompany;
use App\Rules\IsValidDate;
use App\Rules\IsValidSupplier;
use App\Rules\IsValidWarehouse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PurchaseDirectStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', Purchase::class);
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'supplier_id' => $this->filled('supplier_id') ? HashidsHelper::decodeId($this->supplier_id) : null,
            'purchase_order_id' => $this->filled('purchase_order_id') ? HashidsHelper::decodeId($this->purchase_order_id) : null,
            'direct_receipt_warehouse_id' => $this->filled('direct_receipt_warehouse_id') ? HashidsHelper::decodeId($this->direct_receipt_warehouse_id) : null,
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

        if (is_array($this->input('additional_costs'))) {
            $additionalCosts = [];
            foreach ($this->input('additional_costs') as $additionalCost) {
                if (array_key_exists('purchase_additional_cost_category_id', $additionalCost) && ! is_null($additionalCost['purchase_additional_cost_category_id'])) {
                    $additionalCost['purchase_additional_cost_category_id'] = HashidsHelper::decodeId($additionalCost['purchase_additional_cost_category_id']);
                }
                if (array_key_exists('paid_immediately_cash_account_id', $additionalCost) && ! is_null($additionalCost['paid_immediately_cash_account_id'])) {
                    $additionalCost['paid_immediately_cash_account_id'] = HashidsHelper::decodeId($additionalCost['paid_immediately_cash_account_id']);
                }
                $additionalCosts[] = $additionalCost;
            }
            $this->merge(['additional_costs' => $additionalCosts]);
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
            'supplier_id' => [
                'required',
                'integer',
                'bail',
                new ExistsForCompany('suppliers', $this->company_id),
                new IsValidSupplier($this->company_id),
            ],
            'purchase_order_id' => ['present', 'nullable', 'integer', new ExistsForCompany('purchase_orders', $this->company_id)],
            'direct_receipt_warehouse_id' => [
                'required',
                'integer',
                'bail',
                new ExistsForCompany('warehouses', $this->company_id),
                new IsValidWarehouse($this->company_id, false),
            ],
            'tax_invoice_number' => ['present', 'nullable', 'string'],
            'tax_invoice_vat_base' => ['required', 'numeric', 'min:0'],
            'tax_invoice_vat' => ['required', 'numeric', 'min:0'],
            'remarks' => ['present', 'nullable', 'string'],
            'is_posted' => ['required', 'boolean'],
            'additional_cost' => ['required', 'numeric', 'min:0'],
            'rounding' => ['required', 'numeric'],

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
            'items.*.product_unit_price_discounts' => ['present', 'array'],
            'items.*.product_unit_price_discounts.*.sequence' => ['required', 'integer', 'min:1'],
            'items.*.product_unit_price_discounts.*.discount_type' => ['required', Rule::enum(DiscountTypeEnum::class)],
            'items.*.product_unit_price_discounts.*.discount_value' => ['required', 'numeric', 'min:0'],
            'items.*.subtotal_discounts' => ['present', 'array'],
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
            'items.*.serials' => ['present', 'array'],
            'items.*.serials.*.serial' => ['required', 'string'],

            'global_discounts' => ['present', 'array'],
            'global_discounts.*.sequence' => ['required', 'integer', 'min:1'],
            'global_discounts.*.discount_type' => ['required', Rule::enum(DiscountTypeEnum::class)],
            'global_discounts.*.discount_value' => ['required', 'numeric', 'min:0'],

            'additional_costs' => ['present', 'array'],
            'additional_costs.*.code' => ['required', 'string'],
            'additional_costs.*.date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'additional_costs.*.due_days' => ['required', 'integer', 'min:0'],
            'additional_costs.*.purchase_additional_cost_category_id' => [
                'required',
                'integer',
                'bail',
                new ExistsForCompany('purchase_additional_cost_categories', $this->company_id),
            ],
            'additional_costs.*.paid_immediately_cash_account_id' => [
                'present',
                'nullable',
                'integer',
                'bail',
                new ExistsForCompany('cash_accounts', $this->company_id),
                new IsValidCashAccount($this->branch_id),
            ],
            'additional_costs.*.amount_paid_immediately' => ['required', 'numeric', 'min:0'],
            'additional_costs.*.amount_payable' => ['required', 'numeric', 'min:0'],
            'additional_costs.*.remarks' => ['present', 'nullable', 'string'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $purchaseOrderId = $this->input('purchase_order_id');

            if (is_null($purchaseOrderId)) {
                foreach ($this->input('items', []) as $index => $item) {
                    if (! empty($item['purchase_order_item_id'])) {
                        $validator->errors()->add(
                            "items.$index.purchase_order_item_id",
                            trans('rules.purchase.purchase_order_item_must_be_empty_without_purchase_order')
                        );
                    }
                }
            } else {
                $purchaseOrder = PurchaseOrder::with('items:id,purchase_order_id')->find($purchaseOrderId);

                if (! is_null($purchaseOrder)) {
                    if ((int) $this->input('supplier_id') !== (int) $purchaseOrder->supplier_id) {
                        $validator->errors()->add('purchase_order_id', trans('rules.purchase.purchase_order_supplier_must_match'));
                    }

                    if ((int) $this->input('branch_id') !== (int) $purchaseOrder->branch_id) {
                        $validator->errors()->add('purchase_order_id', trans('rules.purchase.purchase_order_branch_must_match'));
                    }

                    $purchaseOrderItemIds = $purchaseOrder->items->pluck('id')->all();
                    foreach ($this->input('items', []) as $index => $item) {
                        $purchaseOrderItemId = $item['purchase_order_item_id'] ?? null;

                        if (! is_null($purchaseOrderItemId) && ! in_array($purchaseOrderItemId, $purchaseOrderItemIds, true)) {
                            $validator->errors()->add(
                                "items.$index.purchase_order_item_id",
                                trans('rules.purchase.invalid_purchase_order_item_reference')
                            );
                        }
                    }
                }
            }

            foreach ($this->input('additional_costs', []) as $index => $additionalCost) {
                $amountPaidImmediately = (float) ($additionalCost['amount_paid_immediately'] ?? 0);
                $amountPayable = (float) ($additionalCost['amount_payable'] ?? 0);
                $code = $additionalCost['code'] ?? null;

                if ($amountPaidImmediately > 0 && empty($additionalCost['paid_immediately_cash_account_id'])) {
                    $validator->errors()->add(
                        "additional_costs.$index.paid_immediately_cash_account_id",
                        trans('validation.required', [
                            'attribute' => trans('validation_attributes.purchase_additional_cost.paid_immediately_cash_account_id'),
                        ])
                    );
                }

                if ($amountPaidImmediately <= 0 && $amountPayable <= 0) {
                    $validator->errors()->add(
                        "additional_costs.$index.amount_total",
                        trans('rules.purchase.additional_cost_amount_total_must_be_positive')
                    );
                }

                if (
                    ! empty($code)
                    && $code !== config('dcslab.KEYWORDS.AUTO')
                    && PurchaseAdditionalCost::where('company_id', $this->company_id)
                        ->whereNull('deleted_at')
                        ->where('code', $code)
                        ->exists()
                ) {
                    $validator->errors()->add("additional_costs.$index.code", trans('rules.unique_code'));
                }
            }

            foreach (($validator->getData()['items'] ?? []) as $index => $item) {
                $productUnitId = $item['product_unit_id'] ?? null;
                $qty = $item['qty'] ?? null;
                $conversionValue = $item['product_unit_conversion_value'] ?? null;
                $serials = $item['serials'] ?? [];

                if (empty($productUnitId) || ! is_numeric($qty) || ! is_numeric($conversionValue)) {
                    continue;
                }

                $product = ProductUnit::with('product')->find($productUnitId)?->product;
                if (! $product?->is_use_serial_number) {
                    continue;
                }

                $baseQty = bcmul((string) $qty, (string) $conversionValue, 8);
                $normalizedBaseQty = rtrim(rtrim($baseQty, '0'), '.');
                if (str_contains($normalizedBaseQty, '.')) {
                    $validator->errors()->add('items.'.$index.'.serials', trans('rules.stock_transfer.serial_base_qty_must_be_integer'));

                    continue;
                }

                $serialValues = collect(is_array($serials) ? $serials : [])
                    ->pluck('serial')
                    ->values();
                if ($serialValues->count() !== $serialValues->unique()->count()) {
                    $validator->errors()->add('items.'.$index.'.serials', trans('rules.purchase.duplicate_serial'));
                }

                $serialCount = (string) count(is_array($serials) ? $serials : []);
                if (bccomp($serialCount, $baseQty, 8) !== 0) {
                    $validator->errors()->add('items.'.$index.'.serials', trans('rules.stock_transfer.serial_count_must_match_base_qty'));
                }
            }
        });
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.purchase.company_id'),
            'branch_id' => trans('validation_attributes.purchase.branch_id'),
            'code' => trans('validation_attributes.purchase.code'),
            'date' => trans('validation_attributes.purchase.date'),
            'due_days' => trans('validation_attributes.purchase.due_days'),
            'supplier_id' => trans('validation_attributes.purchase.supplier_id'),
            'purchase_order_id' => trans('validation_attributes.purchase.purchase_order_id'),
            'direct_receipt_warehouse_id' => trans('validation_attributes.purchase.direct_receipt_warehouse_id'),
            'tax_invoice_number' => trans('validation_attributes.purchase.tax_invoice_number'),
            'tax_invoice_vat_base' => trans('validation_attributes.purchase.tax_invoice_vat_base'),
            'tax_invoice_vat' => trans('validation_attributes.purchase.tax_invoice_vat'),
            'remarks' => trans('validation_attributes.purchase.remarks'),

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
            'items.*.serials' => 'serials',

            'global_discounts.*.sequence' => trans('validation_attributes.purchase_order_global_discount.sequence'),
            'global_discounts.*.discount_type' => trans('validation_attributes.purchase_order_global_discount.discount_type'),
            'global_discounts.*.discount_value' => trans('validation_attributes.purchase_order_global_discount.discount_value'),

            'additional_costs.*.purchase_additional_cost_category_id' => trans('validation_attributes.purchase_additional_cost.purchase_additional_cost_category_id'),
            'additional_costs.*.code' => trans('validation_attributes.purchase_additional_cost.code'),
            'additional_costs.*.date' => trans('validation_attributes.purchase_additional_cost.date'),
            'additional_costs.*.due_days' => trans('validation_attributes.purchase_additional_cost.due_days'),
            'additional_costs.*.paid_immediately_cash_account_id' => trans('validation_attributes.purchase_additional_cost.paid_immediately_cash_account_id'),
            'additional_costs.*.amount_paid_immediately' => trans('validation_attributes.purchase_additional_cost.amount_paid_immediately'),
            'additional_costs.*.amount_payable' => trans('validation_attributes.purchase_additional_cost.amount_payable'),
            'additional_costs.*.remarks' => trans('validation_attributes.purchase_additional_cost.remarks'),
        ];
    }
}
