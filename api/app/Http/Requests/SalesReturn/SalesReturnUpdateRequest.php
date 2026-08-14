<?php

namespace App\Http\Requests\SalesReturn;

use App\Enums\PaymentTypeEnum;
use App\Helpers\HashidsHelper;
use App\Models\ProductUnit;
use App\Models\SalesInvoice;
use App\Models\SalesOrderDeliveryItem;
use App\Models\SalesReturnItem;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidCustomer;
use App\Rules\IsValidDate;
use App\Rules\IsValidWarehouse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SalesReturnUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $salesReturn = $this->route('sales_return');

        return $user->can('update', $salesReturn);
    }

    public function prepareForValidation()
    {
        $salesReturn = $this->route('sales_return');

        $deleteItemIds = collect($this->input('delete_item_ids', []))
            ->map(fn ($id) => HashidsHelper::decodeId($id))
            ->all();
        $deleteRefundIds = collect($this->input('delete_refund_ids', []))
            ->map(fn ($id) => HashidsHelper::decodeId($id))
            ->all();

        $this->merge([
            'company_id' => $salesReturn->company_id,
            'branch_id' => $salesReturn->branch_id,
            'customer_id' => $this->filled('customer_id') ? HashidsHelper::decodeId($this->customer_id) : null,
            'sales_invoice_id' => $this->filled('sales_invoice_id') ? HashidsHelper::decodeId($this->sales_invoice_id) : null,
            'warehouse_id' => $this->filled('warehouse_id') ? HashidsHelper::decodeId($this->warehouse_id) : null,
            'remarks' => $this->filled('remarks') ? $this['remarks'] : null,
            'delete_item_ids' => $deleteItemIds,
            'delete_refund_ids' => $deleteRefundIds,
        ]);

        if (is_array($this->input('items'))) {
            $items = [];
            foreach ($this->input('items') as $item) {
                if (! is_array($item)) {
                    $items[] = $item;

                    continue;
                }

                if (! empty($item['id'])) {
                    $item['id'] = HashidsHelper::decodeId($item['id']);
                } else {
                    $item['id'] = null;
                }

                if (! empty($item['sales_order_delivery_item_id'])) {
                    $item['sales_order_delivery_item_id'] = HashidsHelper::decodeId($item['sales_order_delivery_item_id']);
                } else {
                    $item['sales_order_delivery_item_id'] = null;
                }

                if (isset($item['product_unit_id'])) {
                    $item['product_unit_id'] = HashidsHelper::decodeId($item['product_unit_id']);
                }

                if (! empty($item['vat_profile_id'])) {
                    $item['vat_profile_id'] = HashidsHelper::decodeId($item['vat_profile_id']);
                } else {
                    $item['vat_profile_id'] = null;
                }

                $item['remarks'] = $item['remarks'] ?? null;

                $item['delete_serial_ids'] = collect($item['delete_serial_ids'] ?? [])
                    ->map(fn ($id) => HashidsHelper::decodeId($id))
                    ->all();

                $serials = [];
                foreach ($item['serials'] ?? [] as $serial) {
                    if (is_array($serial) && ! empty($serial['id'])) {
                        $serial['id'] = HashidsHelper::decodeId($serial['id']);
                    } elseif (is_array($serial)) {
                        $serial['id'] = null;
                    }

                    $serials[] = $serial;
                }
                $item['serials'] = $serials;

                $items[] = $item;
            }

            $this->merge(['items' => $items]);
        }

        if (is_array($this->input('refunds'))) {
            $refunds = [];
            foreach ($this->input('refunds') as $refund) {
                if (! is_array($refund)) {
                    $refunds[] = $refund;

                    continue;
                }

                if (! empty($refund['id'])) {
                    $refund['id'] = HashidsHelper::decodeId($refund['id']);
                } else {
                    $refund['id'] = null;
                }

                if (isset($refund['cash_account_id'])) {
                    $refund['cash_account_id'] = HashidsHelper::decodeId($refund['cash_account_id']);
                }

                $refund['remarks'] = $refund['remarks'] ?? null;

                $refunds[] = $refund;
            }

            $this->merge(['refunds' => $refunds]);
        }
    }

    public function rules()
    {
        $salesReturn = $this->route('sales_return');

        return [
            'company_id' => ['required', 'integer'],
            'branch_id' => ['required', 'integer'],
            'code' => ['required', 'string', 'max:255'],
            'date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'customer_id' => ['required', 'integer', 'bail', new IsValidCustomer()],
            'sales_invoice_id' => ['present', 'nullable', 'integer', new ExistsForCompany('sales_invoices', $this->company_id)],
            'warehouse_id' => ['required', 'integer', new IsValidWarehouse($this->company_id, false)],
            'global_discount' => ['required', 'numeric', 'min:0'],
            'rounding' => ['required', 'numeric'],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],
            'is_posted' => ['required', 'boolean'],

            'delete_item_ids' => ['present', 'array'],
            'delete_item_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('sales_return_items', 'id')->where(function ($query) use ($salesReturn) {
                    $query->where('company_id', $this->company_id)
                        ->where('sales_return_id', $salesReturn->id);
                }),
            ],

            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => [
                'present',
                'nullable',
                'integer',
                Rule::exists('sales_return_items', 'id')->where(function ($query) use ($salesReturn) {
                    $query->where('company_id', $this->company_id)
                        ->where('sales_return_id', $salesReturn->id);
                }),
            ],
            'items.*.sales_order_delivery_item_id' => ['present', 'nullable', 'integer', new ExistsForCompany('sales_order_delivery_items', $this->company_id)],
            'items.*.qty' => ['required', 'numeric', 'min:0.00000001'],
            'items.*.product_unit_id' => ['required', 'integer', new ExistsForCompany('product_units', $this->company_id)],
            'items.*.product_unit_conversion_value' => ['required', 'numeric', 'min:1'],
            'items.*.product_unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.product_unit_is_price_include_vat' => ['required', 'boolean'],
            'items.*.price_discount' => ['required', 'numeric', 'min:0'],
            'items.*.subtotal_discount' => ['required', 'numeric', 'min:0'],
            'items.*.vat_profile_id' => ['present', 'nullable', 'integer', new ExistsForCompany('vat_profiles', $this->company_id)],
            'items.*.vat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.vat_base_numerator' => ['required', 'integer', 'min:1'],
            'items.*.vat_base_denominator' => ['required', 'integer', 'min:1'],
            'items.*.remarks' => ['present', 'nullable', 'string', 'max:255'],

            'items.*.delete_serial_ids' => ['present', 'array'],
            'items.*.delete_serial_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('sales_return_item_serials', 'id')->where(function ($query) use ($salesReturn) {
                    $query->where('company_id', $this->company_id)
                        ->where('sales_return_id', $salesReturn->id);
                }),
            ],
            'items.*.serials' => ['present', 'array'],
            'items.*.serials.*.id' => [
                'present',
                'nullable',
                'integer',
                Rule::exists('sales_return_item_serials', 'id')->where(function ($query) use ($salesReturn) {
                    $query->where('company_id', $this->company_id)
                        ->where('sales_return_id', $salesReturn->id);
                }),
            ],
            'items.*.serials.*.serial' => ['required', 'string', 'max:255'],

            'delete_refund_ids' => ['present', 'array'],
            'delete_refund_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('sales_return_refunds', 'id')->where(function ($query) use ($salesReturn) {
                    $query->where('company_id', $this->company_id)
                        ->where('sales_return_id', $salesReturn->id);
                }),
            ],

            'refunds' => ['present', 'array'],
            'refunds.*.id' => [
                'present',
                'nullable',
                'integer',
                Rule::exists('sales_return_refunds', 'id')->where(function ($query) use ($salesReturn) {
                    $query->where('company_id', $this->company_id)
                        ->where('sales_return_id', $salesReturn->id);
                }),
            ],
            'refunds.*.code' => ['required', 'string', 'max:255'],
            'refunds.*.date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'refunds.*.cash_account_id' => [
                'required',
                'integer',
                'bail',
                new ExistsForCompany('cash_accounts', $this->company_id),
                new IsValidCashAccount($this->branch_id),
            ],
            'refunds.*.amount' => ['required', 'numeric', 'gt:0'],
            'refunds.*.remarks' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $salesReturn = $this->route('sales_return');
            $data = $validator->getData();
            $items = $data['items'] ?? [];
            $salesInvoiceId = $data['sales_invoice_id'] ?? null;
            $customerId = $data['customer_id'] ?? null;
            $branchId = $data['branch_id'] ?? null;

            if (! $salesInvoiceId && $salesReturn->invoicePayments()->exists()) {
                $validator->errors()->add('sales_invoice_id', trans('rules.sales_return.sales_invoice_required_when_allocated'));
            }

            if ($salesInvoiceId) {
                $salesInvoice = SalesInvoice::query()->find($salesInvoiceId);

                if ($salesInvoice) {
                    if (filled($customerId) && (int) $salesInvoice->customer_id !== (int) $customerId) {
                        $validator->errors()->add('customer_id', trans('rules.sales_return.sales_invoice_customer_must_match'));
                    }

                    if (filled($branchId) && (int) $salesInvoice->branch_id !== (int) $branchId) {
                        $validator->errors()->add('branch_id', trans('rules.sales_return.sales_invoice_branch_must_match'));
                    }
                }
            } else {
                foreach ($items as $index => $item) {
                    if ((float) ($item['vat_rate'] ?? 0) > 0) {
                        $validator->errors()->add('items.'.$index.'.vat_rate', trans('rules.sales_return.vat_must_be_zero_without_invoice'));
                    }
                }
            }

            $this->validateDuplicateProducts($validator, $items);
            $this->validateDeliveryItemLinks($validator, $items, $customerId, $salesReturn->id);
            $this->validateSerials($validator, $items);
            $this->validateDuplicateSerialsInDocument($validator, $items);

            $amountAllocatedToInvoice = (float) $salesReturn->invoicePayments()
                ->where('payment_type', PaymentTypeEnum::RETURN->value)
                ->sum('amount');
            $this->validateRefundCap($validator, $data, $amountAllocatedToInvoice);
        });
    }

    private function validateDuplicateProducts($validator, array $items): void
    {
        $productIds = [];
        foreach ($items as $index => $item) {
            $productUnitId = $item['product_unit_id'] ?? null;
            if (empty($productUnitId)) {
                continue;
            }

            $productId = ProductUnit::query()->whereKey($productUnitId)->value('product_id');
            if (is_null($productId)) {
                continue;
            }

            if (in_array($productId, $productIds, true)) {
                $validator->errors()->add('items.'.$index.'.product_unit_id', trans('rules.sales_return.duplicate_product'));

                continue;
            }

            $productIds[] = $productId;
        }
    }

    private function validateDeliveryItemLinks($validator, array $items, $customerId, ?int $exceptSalesReturnId): void
    {
        $requestReturnedBaseQtyPerDeliveryItem = [];
        foreach ($items as $item) {
            $deliveryItemId = $item['sales_order_delivery_item_id'] ?? null;
            if (empty($deliveryItemId)) {
                continue;
            }

            $baseQty = (float) ($item['qty'] ?? 0) * (float) ($item['product_unit_conversion_value'] ?? 0);
            $requestReturnedBaseQtyPerDeliveryItem[$deliveryItemId] =
                ($requestReturnedBaseQtyPerDeliveryItem[$deliveryItemId] ?? 0) + $baseQty;
        }

        foreach ($items as $index => $item) {
            $deliveryItemId = $item['sales_order_delivery_item_id'] ?? null;
            if (empty($deliveryItemId)) {
                continue;
            }

            $deliveryItem = SalesOrderDeliveryItem::query()->find($deliveryItemId);
            if (! $deliveryItem) {
                continue;
            }

            $productUnitId = $item['product_unit_id'] ?? null;
            $productId = $productUnitId
                ? ProductUnit::query()->whereKey($productUnitId)->value('product_id')
                : null;

            if (is_null($productId) || (int) $deliveryItem->product_id !== (int) $productId) {
                $validator->errors()->add('items.'.$index.'.sales_order_delivery_item_id', trans('rules.sales_return.invalid_sales_order_delivery_item_reference'));

                continue;
            }

            if ((int) $deliveryItem->salesOrderDelivery?->customer_id !== (int) $customerId) {
                $validator->errors()->add('items.'.$index.'.sales_order_delivery_item_id', trans('rules.sales_return.invalid_sales_order_delivery_item_reference'));

                continue;
            }

            $alreadyReturnedQuery = SalesReturnItem::query()
                ->where('sales_order_delivery_item_id', $deliveryItemId);
            if ($exceptSalesReturnId) {
                $alreadyReturnedQuery->where('sales_return_id', '<>', $exceptSalesReturnId);
            }
            $alreadyReturnedBaseQty = (float) $alreadyReturnedQuery->sum('product_unit_qty_base');

            $requestedBaseQty = (float) ($requestReturnedBaseQtyPerDeliveryItem[$deliveryItemId] ?? 0);
            $deliveredBaseQty = (float) $deliveryItem->product_unit_qty_base;

            if (round($alreadyReturnedBaseQty + $requestedBaseQty - $deliveredBaseQty, 8) > 0) {
                $validator->errors()->add('items.'.$index.'.qty', trans('rules.sales_return.return_qty_exceeds_delivered_qty'));
            }
        }
    }

    private function validateSerials($validator, array $items): void
    {
        foreach ($items as $index => $item) {
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
                // NOTE: no sales_return-specific key exists; reuse the stock_transfer serial keys.
                $validator->errors()->add('items.'.$index.'.serials', trans('rules.stock_transfer.serial_base_qty_must_be_integer'));

                continue;
            }

            $serialCount = (string) count(is_array($serials) ? $serials : []);
            if (bccomp($serialCount, $baseQty, 8) !== 0) {
                $validator->errors()->add('items.'.$index.'.serials', trans('rules.stock_transfer.serial_count_must_match_base_qty'));
            }
        }
    }

    /**
     * A serial may only appear once across ALL items of the document, not just
     * within a single item.
     */
    private function validateDuplicateSerialsInDocument($validator, array $items): void
    {
        $seenSerials = [];

        foreach ($items as $index => $item) {
            $serials = $item['serials'] ?? [];

            if (! is_array($serials)) {
                continue;
            }

            foreach ($serials as $serialIndex => $serial) {
                $serialValue = is_array($serial) ? ($serial['serial'] ?? null) : null;

                if (! is_string($serialValue) || $serialValue === '') {
                    continue;
                }

                if (isset($seenSerials[$serialValue])) {
                    $validator->errors()->add(
                        "items.$index.serials.$serialIndex.serial",
                        trans('rules.sales_return.duplicate_serial_in_document')
                    );

                    continue;
                }

                $seenSerials[$serialValue] = true;
            }
        }
    }

    private function validateRefundCap($validator, array $data, float $amountAllocatedToInvoice): void
    {
        $refundTotal = collect($data['refunds'] ?? [])
            ->sum(fn (array $refund) => (float) ($refund['amount'] ?? 0));

        if ($refundTotal <= 0) {
            return;
        }

        $expectedAmountPayable = $this->computeExpectedAmountPayable(
            items: $data['items'] ?? [],
            globalDiscount: (float) ($data['global_discount'] ?? 0),
            rounding: (float) ($data['rounding'] ?? 0),
        );

        if (round($refundTotal - ($expectedAmountPayable - $amountAllocatedToInvoice), 8) > 0) {
            $validator->errors()->add('refunds', trans('rules.sales_return.refunds_exceed_amount_available'));
        }
    }

    /**
     * Replays the totals waterfall (single nominal discounts, pro-rated global
     * discount, VAT chain, rounding) over the request payload so the refund
     * cap can be validated before anything is persisted.
     */
    private function computeExpectedAmountPayable(array $items, float $globalDiscount, float $rounding): float
    {
        $rows = [];
        foreach ($items as $item) {
            $qty = (float) ($item['qty'] ?? 0);
            $price = (float) ($item['product_unit_price'] ?? 0);
            $priceDiscount = min(max((float) ($item['price_discount'] ?? 0), 0), $price);
            $subtotal = $qty * ($price - $priceDiscount);
            $subtotalDiscount = min(max((float) ($item['subtotal_discount'] ?? 0), 0), $subtotal);

            $rows[] = [
                'subtotal_after_discount' => $subtotal - $subtotalDiscount,
                'is_price_include_vat' => (bool) ($item['product_unit_is_price_include_vat'] ?? false),
                'vat_rate' => (float) ($item['vat_rate'] ?? 0),
                'vat_base_numerator' => (int) ($item['vat_base_numerator'] ?? 1),
                'vat_base_denominator' => (int) ($item['vat_base_denominator'] ?? 1),
            ];
        }

        $itemTotalBeforeGlobalDiscount = array_sum(array_column($rows, 'subtotal_after_discount'));
        $globalDiscount = min(max($globalDiscount, 0), $itemTotalBeforeGlobalDiscount);

        $itemTotalAfterVat = 0.0;
        foreach ($rows as $row) {
            $subtotalAfterGlobalDiscount = $row['subtotal_after_discount'];
            if ($itemTotalBeforeGlobalDiscount > 0 && $globalDiscount > 0) {
                $subtotalAfterGlobalDiscount -= ($row['subtotal_after_discount'] / $itemTotalBeforeGlobalDiscount) * $globalDiscount;
            }

            $vatRate = $row['vat_rate'];
            $vatBaseFactor = $row['vat_base_denominator'] > 0
                ? $row['vat_base_numerator'] / $row['vat_base_denominator']
                : 0;

            $vatBase = 0.0;
            if ($subtotalAfterGlobalDiscount > 0 && $vatRate > 0 && $vatBaseFactor > 0) {
                $vatBase = $row['is_price_include_vat']
                    ? ($subtotalAfterGlobalDiscount / (1 + ($vatRate / 100))) * $vatBaseFactor
                    : $subtotalAfterGlobalDiscount * $vatBaseFactor;
            }

            $vat = $vatBase > 0 && $vatRate > 0 ? $vatBase * ($vatRate / 100) : 0.0;

            $itemTotalAfterVat += $row['is_price_include_vat']
                ? $subtotalAfterGlobalDiscount
                : $subtotalAfterGlobalDiscount + $vat;
        }

        return $itemTotalAfterVat + $rounding;
    }

    public function attributes()
    {
        return array_merge(
            trans('validation_attributes.sales_return'),
            [
                'items.*.id' => trans('validation_attributes.sales_return_item.id'),
                'items.*.sales_order_delivery_item_id' => trans('validation_attributes.sales_return_item.sales_order_delivery_item_id'),
                'items.*.qty' => trans('validation_attributes.sales_return_item.qty'),
                'items.*.product_unit_id' => trans('validation_attributes.sales_return_item.product_unit_id'),
                'items.*.product_unit_conversion_value' => trans('validation_attributes.sales_return_item.product_unit_conversion_value'),
                'items.*.product_unit_price' => trans('validation_attributes.sales_return_item.product_unit_price'),
                'items.*.product_unit_is_price_include_vat' => trans('validation_attributes.sales_return_item.product_unit_is_price_include_vat'),
                'items.*.price_discount' => trans('validation_attributes.sales_return_item.price_discount'),
                'items.*.subtotal_discount' => trans('validation_attributes.sales_return_item.subtotal_discount'),
                'items.*.vat_profile_id' => trans('validation_attributes.sales_return_item.vat_profile_id'),
                'items.*.vat_rate' => trans('validation_attributes.sales_return_item.vat_rate'),
                'items.*.vat_base_numerator' => trans('validation_attributes.sales_return_item.vat_base_numerator'),
                'items.*.vat_base_denominator' => trans('validation_attributes.sales_return_item.vat_base_denominator'),
                'items.*.remarks' => trans('validation_attributes.sales_return_item.remarks'),
                'items.*.serials.*.id' => trans('validation_attributes.sales_return_item_serial.id'),
                'items.*.serials.*.serial' => trans('validation_attributes.sales_return_item_serial.serial'),

                'refunds.*.id' => trans('validation_attributes.sales_return_refund.id'),
                'refunds.*.code' => trans('validation_attributes.sales_return_refund.code'),
                'refunds.*.date' => trans('validation_attributes.sales_return_refund.date'),
                'refunds.*.cash_account_id' => trans('validation_attributes.sales_return_refund.cash_account_id'),
                'refunds.*.amount' => trans('validation_attributes.sales_return_refund.amount'),
                'refunds.*.remarks' => trans('validation_attributes.sales_return_refund.remarks'),
            ],
        );
    }

    public function messages()
    {
        return [
            'delete_item_ids.*.exists' => trans('rules.sales_return.invalid_delete_item_reference'),
            'items.*.id.exists' => trans('rules.sales_return.invalid_item_reference'),
            'items.*.delete_serial_ids.*.exists' => trans('rules.sales_return.invalid_serial_reference'),
            'items.*.serials.*.id.exists' => trans('rules.sales_return.invalid_serial_reference'),
            'delete_refund_ids.*.exists' => trans('rules.sales_return.invalid_delete_refund_reference'),
            'refunds.*.id.exists' => trans('rules.sales_return.invalid_refund_reference'),
        ];
    }
}
