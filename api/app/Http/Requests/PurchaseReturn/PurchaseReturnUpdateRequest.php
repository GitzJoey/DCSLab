<?php

namespace App\Http\Requests\PurchaseReturn;

use App\Enums\PaymentTypeEnum;
use App\Helpers\HashidsHelper;
use App\Models\ProductUnit;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrderReceiptItem;
use App\Models\PurchaseReturnItem;
use App\Models\PurchaseReturnItemSerial;
use App\Models\PurchaseReturnRefund;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidDate;
use App\Rules\IsValidSupplier;
use App\Rules\IsValidWarehouse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PurchaseReturnUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $purchaseReturn = $this->route('purchase_return');

        return $user->can('update', $purchaseReturn);
    }

    public function prepareForValidation()
    {
        $purchaseReturn = $this->route('purchase_return');

        $deleteItemIds = collect($this->input('delete_item_ids', []))
            ->map(fn ($id) => HashidsHelper::decodeId($id))
            ->all();
        $deleteRefundIds = collect($this->input('delete_refund_ids', []))
            ->map(fn ($id) => HashidsHelper::decodeId($id))
            ->all();

        $this->merge([
            'company_id' => $purchaseReturn->company_id,
            'branch_id' => $purchaseReturn->branch_id,
            'supplier_id' => $this->filled('supplier_id') ? HashidsHelper::decodeId($this->supplier_id) : null,
            'purchase_invoice_id' => $this->filled('purchase_invoice_id') ? HashidsHelper::decodeId($this->purchase_invoice_id) : null,
            'warehouse_id' => $this->filled('warehouse_id') ? HashidsHelper::decodeId($this->warehouse_id) : null,
            'remarks' => $this->filled('remarks') ? $this['remarks'] : null,
            'delete_item_ids' => $deleteItemIds,
            'delete_refund_ids' => $deleteRefundIds,
        ]);

        if (is_array($this->input('items'))) {
            $items = [];
            foreach ($this->input('items') as $item) {
                if (is_array($item)) {
                    if (array_key_exists('id', $item) && ! is_null($item['id'])) {
                        $item['id'] = HashidsHelper::decodeId($item['id']);
                    }

                    if (isset($item['product_unit_id'])) {
                        $item['product_unit_id'] = HashidsHelper::decodeId($item['product_unit_id']);
                    }

                    if (array_key_exists('vat_profile_id', $item) && ! is_null($item['vat_profile_id'])) {
                        $item['vat_profile_id'] = HashidsHelper::decodeId($item['vat_profile_id']);
                    }

                    if (array_key_exists('purchase_order_receipt_item_id', $item) && ! is_null($item['purchase_order_receipt_item_id'])) {
                        $item['purchase_order_receipt_item_id'] = HashidsHelper::decodeId($item['purchase_order_receipt_item_id']);
                    }

                    $item['id'] = $item['id'] ?? null;
                    $item['purchase_order_receipt_item_id'] = $item['purchase_order_receipt_item_id'] ?? null;
                    $item['vat_profile_id'] = $item['vat_profile_id'] ?? null;
                    $item['remarks'] = $item['remarks'] ?? null;

                    $item['delete_serial_ids'] = collect($item['delete_serial_ids'] ?? [])
                        ->map(fn ($id) => HashidsHelper::decodeId($id))
                        ->all();

                    $serials = [];
                    foreach ($item['serials'] ?? [] as $serial) {
                        if (is_array($serial)) {
                            if (array_key_exists('id', $serial) && ! is_null($serial['id'])) {
                                $serial['id'] = HashidsHelper::decodeId($serial['id']);
                            }

                            $serial['id'] = $serial['id'] ?? null;
                        }

                        $serials[] = $serial;
                    }
                    $item['serials'] = $serials;
                }

                $items[] = $item;
            }

            $this->merge(['items' => $items]);
        }

        if (is_array($this->input('refunds'))) {
            $refunds = [];
            foreach ($this->input('refunds') as $refund) {
                if (is_array($refund)) {
                    if (array_key_exists('id', $refund) && ! is_null($refund['id'])) {
                        $refund['id'] = HashidsHelper::decodeId($refund['id']);
                    }

                    if (array_key_exists('cash_account_id', $refund) && ! is_null($refund['cash_account_id'])) {
                        $refund['cash_account_id'] = HashidsHelper::decodeId($refund['cash_account_id']);
                    }

                    $refund['id'] = $refund['id'] ?? null;
                    $refund['remarks'] = $refund['remarks'] ?? null;
                }

                $refunds[] = $refund;
            }

            $this->merge(['refunds' => $refunds]);
        }
    }

    public function rules()
    {
        $purchaseReturn = $this->route('purchase_return');

        return [
            'company_id' => ['required', 'integer'],
            'branch_id' => ['required', 'integer'],
            'code' => ['required', 'string', 'max:255'],
            'date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'supplier_id' => ['required', 'integer', 'bail', new ExistsForCompany('suppliers', $this->company_id), new IsValidSupplier($this->company_id)],
            'purchase_invoice_id' => ['present', 'nullable', 'integer', new ExistsForCompany('purchase_invoices', $this->company_id)],
            'warehouse_id' => ['required', 'integer', 'bail', new ExistsForCompany('warehouses', $this->company_id), new IsValidWarehouse($this->company_id, false)],
            'global_discount' => ['required', 'numeric', 'min:0'],
            'rounding' => ['required', 'numeric'],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],
            'is_posted' => ['required', 'boolean'],

            'delete_item_ids' => ['present', 'array'],
            'delete_item_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('purchase_return_items', 'id')->where(function ($query) use ($purchaseReturn) {
                    $query->where('company_id', $this->company_id)
                        ->where('purchase_return_id', $purchaseReturn->id)
                        ->whereNull('deleted_at');
                }),
            ],

            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => [
                'present',
                'nullable',
                'integer',
                Rule::exists('purchase_return_items', 'id')->where(function ($query) use ($purchaseReturn) {
                    $query->where('company_id', $this->company_id)
                        ->where('purchase_return_id', $purchaseReturn->id)
                        ->whereNull('deleted_at');
                }),
            ],
            'items.*.purchase_order_receipt_item_id' => ['present', 'nullable', 'integer', new ExistsForCompany('purchase_order_receipt_items', $this->company_id)],
            'items.*.qty' => ['required', 'numeric', 'gt:0'],
            'items.*.product_unit_id' => ['required', 'integer', new ExistsForCompany('product_units', $this->company_id)],
            'items.*.product_unit_conversion_value' => ['required', 'numeric', 'gt:0'],
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
                Rule::exists('purchase_return_item_serials', 'id')->where(function ($query) use ($purchaseReturn) {
                    $query->where('company_id', $this->company_id)
                        ->where('purchase_return_id', $purchaseReturn->id)
                        ->whereNull('deleted_at');
                }),
            ],
            'items.*.serials' => ['present', 'array'],
            'items.*.serials.*.id' => [
                'present',
                'nullable',
                'integer',
                Rule::exists('purchase_return_item_serials', 'id')->where(function ($query) use ($purchaseReturn) {
                    $query->where('company_id', $this->company_id)
                        ->where('purchase_return_id', $purchaseReturn->id)
                        ->whereNull('deleted_at');
                }),
            ],
            'items.*.serials.*.serial' => ['required', 'string', 'max:255'],

            'delete_refund_ids' => ['present', 'array'],
            'delete_refund_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('purchase_return_refunds', 'id')->where(function ($query) use ($purchaseReturn) {
                    $query->where('company_id', $this->company_id)
                        ->where('purchase_return_id', $purchaseReturn->id)
                        ->whereNull('deleted_at');
                }),
            ],

            'refunds' => ['present', 'array'],
            'refunds.*.id' => [
                'present',
                'nullable',
                'integer',
                Rule::exists('purchase_return_refunds', 'id')->where(function ($query) use ($purchaseReturn) {
                    $query->where('company_id', $this->company_id)
                        ->where('purchase_return_id', $purchaseReturn->id)
                        ->whereNull('deleted_at');
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

            $purchaseReturn = $this->route('purchase_return');
            $items = $validator->getData()['items'] ?? [];
            $refunds = $validator->getData()['refunds'] ?? [];

            $hasInvoicePayments = $purchaseReturn->invoicePayments()->exists();
            $purchaseInvoiceId = $this->input('purchase_invoice_id');

            if ($hasInvoicePayments && ! $purchaseInvoiceId) {
                $validator->errors()->add('purchase_invoice_id', trans('rules.purchase_return.purchase_invoice_required_when_allocated'));
            }

            $productUnitIds = collect($items)->pluck('product_unit_id')->filter()->all();
            $productMap = ProductUnit::query()->whereIn('id', $productUnitIds)->pluck('product_id', 'id');

            $seenProductIds = [];
            foreach ($items as $index => $item) {
                $productId = $productMap[$item['product_unit_id']] ?? null;
                if (! $productId) {
                    continue;
                }

                if (isset($seenProductIds[$productId])) {
                    $validator->errors()->add('items.'.$index.'.product_unit_id', trans('rules.purchase_return.duplicate_product'));
                } else {
                    $seenProductIds[$productId] = true;
                }
            }

            if ($purchaseInvoiceId) {
                $purchaseInvoice = PurchaseInvoice::query()->find($purchaseInvoiceId);

                if ($purchaseInvoice) {
                    if ((int) $purchaseInvoice->supplier_id !== (int) $this->input('supplier_id')) {
                        $validator->errors()->add('supplier_id', trans('rules.purchase_return.purchase_invoice_supplier_must_match'));
                    }

                    if ((int) $purchaseInvoice->branch_id !== (int) $this->input('branch_id')) {
                        $validator->errors()->add('branch_id', trans('rules.purchase_return.purchase_invoice_branch_must_match'));
                    }
                }
            } else {
                foreach ($items as $index => $item) {
                    if ((float) ($item['vat_rate'] ?? 0) > 0) {
                        $validator->errors()->add('items.'.$index.'.vat_rate', trans('rules.purchase_return.vat_must_be_zero_without_invoice'));
                    }
                }
            }

            // Item rows of this return that survive the update untouched (neither
            // submitted nor listed for deletion) still count towards every cap.
            $submittedItemIds = collect($items)->pluck('id')->filter()->map(fn ($id) => (int) $id)->all();
            $deletedItemIds = collect($this->input('delete_item_ids', []))->filter()->map(fn ($id) => (int) $id)->all();
            $handledItemIds = array_unique(array_merge($submittedItemIds, $deletedItemIds));
            $leftoverItems = $purchaseReturn->items()
                ->when($handledItemIds !== [], fn ($query) => $query->whereNotIn('id', $handledItemIds))
                ->get();

            $requestReturnBaseQtyPerReceiptItem = [];
            foreach ($leftoverItems as $leftoverItem) {
                $receiptItemId = $leftoverItem->purchase_order_receipt_item_id;
                if (! $receiptItemId) {
                    continue;
                }

                $requestReturnBaseQtyPerReceiptItem[$receiptItemId] = ($requestReturnBaseQtyPerReceiptItem[$receiptItemId] ?? 0)
                    + (float) $leftoverItem->product_unit_qty_base;
            }

            foreach ($items as $index => $item) {
                $receiptItemId = $item['purchase_order_receipt_item_id'] ?? null;
                if (! $receiptItemId) {
                    continue;
                }

                $receiptItem = PurchaseOrderReceiptItem::query()->find($receiptItemId);
                if (! $receiptItem) {
                    continue;
                }

                $productId = $productMap[$item['product_unit_id']] ?? null;
                if ((int) $receiptItem->product_id !== (int) $productId) {
                    $validator->errors()->add('items.'.$index.'.purchase_order_receipt_item_id', trans('rules.purchase_return.invalid_purchase_order_receipt_item_reference'));

                    continue;
                }

                $baseQty = (float) $item['qty'] * (float) $item['product_unit_conversion_value'];
                $requestReturnBaseQtyPerReceiptItem[$receiptItemId] = ($requestReturnBaseQtyPerReceiptItem[$receiptItemId] ?? 0) + $baseQty;

                $alreadyReturnedBaseQty = (float) PurchaseReturnItem::query()
                    ->where('purchase_order_receipt_item_id', $receiptItemId)
                    ->where('purchase_return_id', '<>', $purchaseReturn->id)
                    ->sum('product_unit_qty_base');

                if ($alreadyReturnedBaseQty + $requestReturnBaseQtyPerReceiptItem[$receiptItemId] > (float) $receiptItem->product_unit_qty_base + 0.00000001) {
                    $validator->errors()->add('items.'.$index.'.qty', trans('rules.purchase_return.return_qty_exceeds_received_qty'));
                }
            }

            foreach ($items as $index => $item) {
                $itemId = $item['id'] ?? null;
                $serialIds = collect($item['serials'] ?? [])->pluck('id')->filter()
                    ->merge(collect($item['delete_serial_ids'] ?? [])->filter())
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values();

                if ($serialIds->isEmpty()) {
                    continue;
                }

                $ownedSerialIds = $itemId
                    ? PurchaseReturnItemSerial::query()
                        ->where('purchase_return_item_id', $itemId)
                        ->whereIn('id', $serialIds->all())
                        ->pluck('id')
                        ->map(fn ($id) => (int) $id)
                        ->all()
                    : [];

                if (count($ownedSerialIds) !== $serialIds->count()) {
                    $validator->errors()->add('items.'.$index.'.serials', trans('rules.purchase_return.invalid_serial_reference'));
                }
            }

            foreach ($items as $index => $item) {
                $qty = $item['qty'] ?? null;
                $conversionValue = $item['product_unit_conversion_value'] ?? null;
                $serials = $item['serials'] ?? [];

                if (! is_numeric($qty) || ! is_numeric($conversionValue)) {
                    continue;
                }

                $product = ProductUnit::with('product')->find($item['product_unit_id'])?->product;
                if (! $product?->is_use_serial_number) {
                    continue;
                }

                $baseQty = bcmul((string) $qty, (string) $conversionValue, 8);
                $normalizedBaseQty = rtrim(rtrim($baseQty, '0'), '.');
                if (str_contains($normalizedBaseQty, '.')) {
                    $validator->errors()->add('items.'.$index.'.serials', trans('rules.stock_transfer.serial_base_qty_must_be_integer'));

                    continue;
                }

                $serialValues = collect($serials)->pluck('serial')->filter();
                if ($serialValues->count() !== $serialValues->unique()->count()) {
                    $validator->errors()->add('items.'.$index.'.serials', trans('rules.purchase_return.duplicate_serial'));
                }

                $serialCount = (string) count(is_array($serials) ? $serials : []);
                if (bccomp($serialCount, $baseQty, 8) !== 0) {
                    $validator->errors()->add('items.'.$index.'.serials', trans('rules.stock_transfer.serial_count_must_match_base_qty'));
                }
            }

            $this->validateDuplicateSerialsInDocument($validator, $items);

            $submittedRefundIds = collect($refunds)->pluck('id')->filter()->map(fn ($id) => (int) $id)->all();
            $deletedRefundIds = collect($this->input('delete_refund_ids', []))->filter()->map(fn ($id) => (int) $id)->all();
            $handledRefundIds = array_unique(array_merge($submittedRefundIds, $deletedRefundIds));
            $leftoverRefundTotal = (float) $purchaseReturn->refunds()
                ->when($handledRefundIds !== [], fn ($query) => $query->whereNotIn('id', $handledRefundIds))
                ->sum('amount');

            $refundTotal = collect($refunds)->sum(fn (array $refund) => (float) ($refund['amount'] ?? 0)) + $leftoverRefundTotal;
            if ($refundTotal > 0) {
                $expectedAmountPayable = $this->computeExpectedAmountPayable(
                    array_merge($items, $leftoverItems->map(fn ($leftoverItem) => [
                        'qty' => (float) $leftoverItem->qty,
                        'product_unit_price' => (float) $leftoverItem->product_unit_price,
                        'price_discount' => (float) $leftoverItem->price_discount,
                        'subtotal_discount' => (float) $leftoverItem->subtotal_discount,
                        'product_unit_is_price_include_vat' => (bool) $leftoverItem->product_unit_is_price_include_vat,
                        'vat_rate' => (float) $leftoverItem->vat_rate,
                        'vat_base_numerator' => (int) $leftoverItem->vat_base_numerator,
                        'vat_base_denominator' => (int) $leftoverItem->vat_base_denominator,
                    ])->all()),
                    (float) $this->input('global_discount', 0),
                    (float) $this->input('rounding', 0),
                );

                $amountAllocatedToInvoice = (float) $purchaseReturn->invoicePayments()
                    ->where('payment_type', PaymentTypeEnum::RETURN->value)
                    ->sum('amount');

                if ($refundTotal > $expectedAmountPayable - $amountAllocatedToInvoice + 0.00000001) {
                    $validator->errors()->add('refunds', trans('rules.purchase_return.refunds_exceed_amount_available'));
                }
            }

            $autoKeyword = config('dcslab.KEYWORDS.AUTO');
            $seenRefundCodes = [];
            foreach ($refunds as $index => $refund) {
                $code = $refund['code'] ?? null;
                if (! $code || $code === $autoKeyword) {
                    continue;
                }

                if (isset($seenRefundCodes[$code])) {
                    $validator->errors()->add('refunds.'.$index.'.code', trans('rules.unique_code'));

                    continue;
                }

                $seenRefundCodes[$code] = true;

                $codeExists = PurchaseReturnRefund::query()
                    ->where('company_id', $this->input('company_id'))
                    ->where('code', $code)
                    ->when($refund['id'] ?? null, fn ($query, $id) => $query->where('id', '<>', $id))
                    ->exists();

                if ($codeExists) {
                    $validator->errors()->add('refunds.'.$index.'.code', trans('rules.unique_code'));
                }
            }
        });
    }

    public function attributes()
    {
        return array_merge(
            trans('validation_attributes.purchase_return'),
            [
                'items.*.id' => trans('validation_attributes.purchase_return_item.id'),
                'items.*.purchase_order_receipt_item_id' => trans('validation_attributes.purchase_return_item.purchase_order_receipt_item_id'),
                'items.*.qty' => trans('validation_attributes.purchase_return_item.qty'),
                'items.*.product_unit_id' => trans('validation_attributes.purchase_return_item.product_unit_id'),
                'items.*.product_unit_conversion_value' => trans('validation_attributes.purchase_return_item.product_unit_conversion_value'),
                'items.*.product_unit_price' => trans('validation_attributes.purchase_return_item.product_unit_price'),
                'items.*.product_unit_is_price_include_vat' => trans('validation_attributes.purchase_return_item.product_unit_is_price_include_vat'),
                'items.*.price_discount' => trans('validation_attributes.purchase_return_item.price_discount'),
                'items.*.subtotal_discount' => trans('validation_attributes.purchase_return_item.subtotal_discount'),
                'items.*.vat_profile_id' => trans('validation_attributes.purchase_return_item.vat_profile_id'),
                'items.*.vat_rate' => trans('validation_attributes.purchase_return_item.vat_rate'),
                'items.*.vat_base_numerator' => trans('validation_attributes.purchase_return_item.vat_base_numerator'),
                'items.*.vat_base_denominator' => trans('validation_attributes.purchase_return_item.vat_base_denominator'),
                'items.*.remarks' => trans('validation_attributes.purchase_return_item.remarks'),
                'items.*.serials.*.id' => trans('validation_attributes.purchase_return_item_serial.id'),
                'items.*.serials.*.serial' => trans('validation_attributes.purchase_return_item_serial.serial'),
                'refunds.*.id' => trans('validation_attributes.purchase_return_refund.id'),
                'refunds.*.code' => trans('validation_attributes.purchase_return_refund.code'),
                'refunds.*.date' => trans('validation_attributes.purchase_return_refund.date'),
                'refunds.*.cash_account_id' => trans('validation_attributes.purchase_return_refund.cash_account_id'),
                'refunds.*.amount' => trans('validation_attributes.purchase_return_refund.amount'),
                'refunds.*.remarks' => trans('validation_attributes.purchase_return_refund.remarks'),
            ],
        );
    }

    public function messages()
    {
        return [
            'delete_item_ids.*.exists' => trans('rules.purchase_return.invalid_delete_item_reference'),
            'items.*.id.exists' => trans('rules.purchase_return.invalid_item_reference'),
            'items.*.delete_serial_ids.*.exists' => trans('rules.purchase_return.invalid_serial_reference'),
            'items.*.serials.*.id.exists' => trans('rules.purchase_return.invalid_serial_reference'),
            'delete_refund_ids.*.exists' => trans('rules.purchase_return.invalid_delete_refund_reference'),
            'refunds.*.id.exists' => trans('rules.purchase_return.invalid_refund_reference'),
        ];
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
                        trans('rules.purchase_return.duplicate_serial_in_document')
                    );

                    continue;
                }

                $seenSerials[$serialValue] = true;
            }
        }
    }

    private function computeExpectedAmountPayable(array $items, float $globalDiscount, float $rounding): float
    {
        $itemRows = [];
        $itemTotalBeforeGlobalDiscount = 0.0;

        foreach ($items as $item) {
            $qty = (float) ($item['qty'] ?? 0);
            $productUnitPrice = (float) ($item['product_unit_price'] ?? 0);
            $priceDiscount = min(max(0, (float) ($item['price_discount'] ?? 0)), $productUnitPrice);
            $subtotal = $qty * ($productUnitPrice - $priceDiscount);
            $subtotalDiscount = min(max(0, (float) ($item['subtotal_discount'] ?? 0)), $subtotal);
            $subtotalAfterDiscount = $subtotal - $subtotalDiscount;

            $itemRows[] = [
                'subtotal_after_discount' => $subtotalAfterDiscount,
                'vat_rate' => (float) ($item['vat_rate'] ?? 0),
                'vat_base_numerator' => (float) ($item['vat_base_numerator'] ?? 1),
                'vat_base_denominator' => (float) ($item['vat_base_denominator'] ?? 1),
                'is_price_include_vat' => (bool) ($item['product_unit_is_price_include_vat'] ?? false),
            ];

            $itemTotalBeforeGlobalDiscount += $subtotalAfterDiscount;
        }

        $effectiveGlobalDiscount = min(max(0, $globalDiscount), $itemTotalBeforeGlobalDiscount);

        $itemTotalAfterVat = 0.0;
        foreach ($itemRows as $itemRow) {
            $subtotalAfterGlobalDiscount = $itemRow['subtotal_after_discount'];
            if ($itemTotalBeforeGlobalDiscount > 0 && $effectiveGlobalDiscount > 0) {
                $subtotalAfterGlobalDiscount -= ($itemRow['subtotal_after_discount'] / $itemTotalBeforeGlobalDiscount) * $effectiveGlobalDiscount;
            }

            $vatRate = $itemRow['vat_rate'];
            $vatBaseFactor = $itemRow['vat_base_denominator'] > 0
                ? $itemRow['vat_base_numerator'] / $itemRow['vat_base_denominator']
                : 0;

            $vat = 0.0;
            if ($subtotalAfterGlobalDiscount > 0 && $vatRate > 0 && $vatBaseFactor > 0) {
                $vatBase = $itemRow['is_price_include_vat']
                    ? ($subtotalAfterGlobalDiscount / (1 + ($vatRate / 100))) * $vatBaseFactor
                    : $subtotalAfterGlobalDiscount * $vatBaseFactor;
                $vat = max(0, $vatBase * ($vatRate / 100));
            }

            $itemTotalAfterVat += $itemRow['is_price_include_vat']
                ? $subtotalAfterGlobalDiscount
                : $subtotalAfterGlobalDiscount + $vat;
        }

        return $itemTotalAfterVat + $rounding;
    }
}
