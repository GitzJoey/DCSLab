<?php

namespace App\Http\Requests\PurchaseInvoice;

use App\Enums\PaymentTypeEnum;
use App\Helpers\HashidsHelper;
use App\Models\ProductUnit;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoicePayment;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrderPayment;
use App\Models\PurchaseReturn;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidCompany;
use App\Rules\IsValidDate;
use App\Rules\IsValidSupplier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PurchaseInvoiceStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', PurchaseInvoice::class);
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'supplier_id' => $this->filled('supplier_id') ? HashidsHelper::decodeId($this->supplier_id) : null,
            'purchase_order_id' => $this->filled('purchase_order_id') ? HashidsHelper::decodeId($this->purchase_order_id) : null,
        ]);

        if (is_array($this->input('items'))) {
            $items = [];
            foreach ($this->input('items') as $item) {
                if (is_array($item)) {
                    foreach (['product_unit_id', 'vat_profile_id', 'purchase_order_item_id'] as $key) {
                        if (array_key_exists($key, $item) && ! is_null($item[$key])) {
                            $item[$key] = HashidsHelper::decodeId($item[$key]);
                        }
                    }
                }
                $items[] = $item;
            }
            $this->merge(['items' => $items]);
        }

        if (is_array($this->input('payments'))) {
            $payments = [];
            foreach ($this->input('payments') as $payment) {
                if (is_array($payment)) {
                    foreach (['cash_account_id', 'purchase_order_payment_id', 'purchase_return_id'] as $key) {
                        if (array_key_exists($key, $payment) && ! is_null($payment[$key])) {
                            $payment[$key] = HashidsHelper::decodeId($payment[$key]);
                        }
                    }
                }
                $payments[] = $payment;
            }
            $this->merge(['payments' => $payments]);
        }
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', 'bail', new IsValidBranch($this->company_id, true)],
            'code' => ['required', 'string', 'max:255'],
            'date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'due_days' => ['required', 'integer', 'min:0'],
            'supplier_id' => [
                'required',
                'integer',
                'bail',
                new ExistsForCompany('suppliers', $this->company_id),
                new IsValidSupplier($this->company_id),
            ],
            'purchase_order_id' => ['required', 'integer', new ExistsForCompany('purchase_orders', $this->company_id)],
            'tax_invoice_number' => ['present', 'nullable', 'string', 'max:255'],
            'tax_invoice_vat_base' => ['required', 'numeric', 'min:0'],
            'tax_invoice_vat' => ['required', 'numeric', 'min:0'],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],
            'is_posted' => ['required', 'boolean'],
            'global_discount' => ['required', 'numeric', 'min:0'],
            'rounding' => ['required', 'numeric'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.qty' => ['required', 'numeric', 'gt:0'],
            'items.*.product_unit_id' => ['required', 'integer', new ExistsForCompany('product_units', $this->company_id)],
            'items.*.product_unit_conversion_value' => ['required', 'numeric', 'gt:0'],
            'items.*.product_unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.product_unit_is_price_include_vat' => ['required', 'boolean'],
            'items.*.price_discount' => ['required', 'numeric', 'min:0'],
            'items.*.subtotal_discount' => ['required', 'numeric', 'min:0'],
            'items.*.vat_profile_id' => [
                'present',
                'nullable',
                'integer',
                new ExistsForCompany('vat_profiles', $this->company_id),
            ],
            'items.*.vat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.vat_base_numerator' => ['required', 'integer', 'min:1'],
            'items.*.vat_base_denominator' => ['required', 'integer', 'min:1'],
            'items.*.purchase_order_item_id' => [
                'present',
                'nullable',
                'integer',
                new ExistsForCompany('purchase_order_items', $this->company_id),
            ],
            'items.*.remarks' => ['present', 'nullable', 'string', 'max:255'],

            'payments' => ['present', 'array'],
            'payments.*.code' => ['required', 'string', 'max:255'],
            'payments.*.date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'payments.*.payment_type' => ['required', Rule::enum(PaymentTypeEnum::class)],
            'payments.*.cash_account_id' => [
                'present',
                'nullable',
                'prohibited_unless:payments.*.payment_type,cash',
                'integer',
                'bail',
                new ExistsForCompany('cash_accounts', $this->company_id),
                new IsValidCashAccount($this->branch_id),
            ],
            'payments.*.purchase_order_payment_id' => [
                'present',
                'nullable',
                'prohibited_unless:payments.*.payment_type,down_payment',
                'integer',
                new ExistsForCompany('purchase_order_payments', $this->company_id),
            ],
            'payments.*.purchase_return_id' => [
                'present',
                'nullable',
                'prohibited_unless:payments.*.payment_type,return',
                'integer',
                new ExistsForCompany('purchase_returns', $this->company_id),
            ],
            'payments.*.amount' => ['required', 'numeric', 'gt:0'],
            'payments.*.remarks' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateDuplicateProducts($validator);
            $this->validatePurchaseOrderLinkage($validator);
            $this->validatePayments($validator, exceptPurchaseInvoiceId: null);
        });
    }

    protected function validateDuplicateProducts($validator): void
    {
        $items = $this->input('items', []);
        $productUnitIds = collect($items)
            ->pluck('product_unit_id')
            ->filter(fn ($productUnitId) => is_numeric($productUnitId))
            ->map(fn ($productUnitId) => (int) $productUnitId)
            ->unique()
            ->values();

        if ($productUnitIds->isEmpty()) {
            return;
        }

        $productIdsByProductUnitId = ProductUnit::query()
            ->whereIn('id', $productUnitIds)
            ->pluck('product_id', 'id')
            ->map(fn ($productId) => (int) $productId)
            ->all();

        $seenProductIds = [];
        foreach ($items as $index => $item) {
            $productUnitId = $item['product_unit_id'] ?? null;
            if (! is_numeric($productUnitId)) {
                continue;
            }

            $productId = $productIdsByProductUnitId[(int) $productUnitId] ?? null;
            if (is_null($productId)) {
                continue;
            }

            if (isset($seenProductIds[$productId])) {
                $validator->errors()->add("items.$index.product_unit_id", trans('rules.purchase_invoice.duplicate_product'));

                continue;
            }

            $seenProductIds[$productId] = true;
        }
    }

    protected function validatePurchaseOrderLinkage($validator): void
    {
        $purchaseOrderId = $this->input('purchase_order_id');
        if (! is_numeric($purchaseOrderId)) {
            return;
        }

        $purchaseOrder = PurchaseOrder::find((int) $purchaseOrderId);
        if (is_null($purchaseOrder)) {
            return;
        }

        if (is_null($purchaseOrder->supplier_id)) {
            $validator->errors()->add('purchase_order_id', trans('rules.purchase_invoice.purchase_order_must_have_supplier'));

            return;
        }

        if ((int) $this->input('supplier_id') !== (int) $purchaseOrder->supplier_id) {
            $validator->errors()->add('purchase_order_id', trans('rules.purchase_invoice.purchase_order_supplier_must_match'));
        }

        if ((int) $this->input('branch_id') !== (int) $purchaseOrder->branch_id) {
            $validator->errors()->add('purchase_order_id', trans('rules.purchase_invoice.purchase_order_branch_must_match'));
        }

        $purchaseOrderItemIds = PurchaseOrderItem::query()
            ->where('purchase_order_id', $purchaseOrder->id)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($this->input('items', []) as $index => $item) {
            $purchaseOrderItemId = $item['purchase_order_item_id'] ?? null;
            if (! is_numeric($purchaseOrderItemId)) {
                continue;
            }

            if (! in_array((int) $purchaseOrderItemId, $purchaseOrderItemIds, true)) {
                $validator->errors()->add(
                    "items.$index.purchase_order_item_id",
                    trans('rules.purchase_invoice.invalid_purchase_order_item_reference')
                );
            }
        }
    }

    protected function validatePayments($validator, ?int $exceptPurchaseInvoiceId): void
    {
        $payments = $this->input('payments', []);
        $purchaseOrderId = is_numeric($this->input('purchase_order_id')) ? (int) $this->input('purchase_order_id') : null;
        $supplierId = is_numeric($this->input('supplier_id')) ? (int) $this->input('supplier_id') : null;

        $purchaseOrderPaymentAmounts = [];
        $purchaseReturnAmounts = [];
        $paymentTotal = 0.0;
        $seenPaymentCodes = [];

        foreach ($payments as $index => $payment) {
            if (! is_array($payment)) {
                continue;
            }

            $paymentType = PaymentTypeEnum::tryFrom((string) ($payment['payment_type'] ?? ''));
            $amount = (float) ($payment['amount'] ?? 0);
            $paymentTotal += $amount;

            $code = $payment['code'] ?? null;
            if (! empty($code) && $code !== config('dcslab.KEYWORDS.AUTO')) {
                if (isset($seenPaymentCodes[$code])) {
                    $validator->errors()->add("payments.$index.code", trans('rules.unique_code'));
                } else {
                    $seenPaymentCodes[$code] = true;

                    $codeQuery = PurchaseInvoicePayment::query()
                        ->where('company_id', $this->company_id)
                        ->where('code', $code)
                        ->whereNull('deleted_at');
                    if (! empty($payment['id'])) {
                        $codeQuery->where('id', '<>', (int) $payment['id']);
                    }
                    if ($codeQuery->exists()) {
                        $validator->errors()->add("payments.$index.code", trans('rules.unique_code'));
                    }
                }
            }

            if ($paymentType === PaymentTypeEnum::CASH && empty($payment['cash_account_id'])) {
                $validator->errors()->add("payments.$index.cash_account_id", trans('validation.required', [
                    'attribute' => trans('validation_attributes.purchase_invoice_payment.cash_account_id'),
                ]));
            }

            if ($paymentType === PaymentTypeEnum::DOWN_PAYMENT) {
                if (empty($payment['purchase_order_payment_id'])) {
                    $validator->errors()->add("payments.$index.purchase_order_payment_id", trans('validation.required', [
                        'attribute' => trans('validation_attributes.purchase_invoice_payment.purchase_order_payment_id'),
                    ]));
                } else {
                    $purchaseOrderPaymentId = (int) $payment['purchase_order_payment_id'];
                    $purchaseOrderPayment = PurchaseOrderPayment::find($purchaseOrderPaymentId);

                    if ($purchaseOrderPayment) {
                        if (! is_null($purchaseOrderId) && (int) $purchaseOrderPayment->purchase_order_id !== $purchaseOrderId) {
                            $validator->errors()->add(
                                "payments.$index.purchase_order_payment_id",
                                trans('rules.purchase_invoice.purchase_order_payment_must_match_purchase_order')
                            );
                        }

                        $purchaseOrderPaymentAmounts[$purchaseOrderPaymentId] = ($purchaseOrderPaymentAmounts[$purchaseOrderPaymentId] ?? 0) + $amount;
                    }
                }
            }

            if ($paymentType === PaymentTypeEnum::RETURN) {
                if (empty($payment['purchase_return_id'])) {
                    $validator->errors()->add("payments.$index.purchase_return_id", trans('validation.required', [
                        'attribute' => trans('validation_attributes.purchase_invoice_payment.purchase_return_id'),
                    ]));
                } else {
                    $purchaseReturnId = (int) $payment['purchase_return_id'];
                    $purchaseReturn = PurchaseReturn::find($purchaseReturnId);

                    if ($purchaseReturn) {
                        if (! is_null($supplierId) && (int) $purchaseReturn->supplier_id !== $supplierId) {
                            $validator->errors()->add(
                                "payments.$index.purchase_return_id",
                                trans('rules.purchase_invoice.purchase_return_supplier_must_match')
                            );
                        }

                        if (is_null($purchaseReturn->purchase_invoice_id)) {
                            $validator->errors()->add(
                                "payments.$index.purchase_return_id",
                                trans('rules.purchase_invoice.purchase_return_must_be_invoiced')
                            );
                        }

                        $purchaseReturnAmounts[$purchaseReturnId] = ($purchaseReturnAmounts[$purchaseReturnId] ?? 0) + $amount;
                    }
                }
            }
        }

        // On update, surviving rows of this invoice that are neither present in
        // the payments input nor listed for deletion keep counting toward the
        // caps (allocatedByOthers below excludes ALL rows of this invoice).
        if ($exceptPurchaseInvoiceId) {
            $inputPaymentIds = collect($payments)
                ->pluck('id')
                ->filter(fn ($id) => is_numeric($id))
                ->map(fn ($id) => (int) $id)
                ->all();
            $deletePaymentIds = collect($this->input('delete_payment_ids', []))
                ->filter(fn ($id) => is_numeric($id))
                ->map(fn ($id) => (int) $id)
                ->all();

            $leftoverPayments = PurchaseInvoicePayment::query()
                ->where('purchase_invoice_id', $exceptPurchaseInvoiceId)
                ->whereNotIn('id', array_merge($inputPaymentIds, $deletePaymentIds))
                ->get();

            foreach ($leftoverPayments as $leftoverPayment) {
                $leftoverAmount = (float) $leftoverPayment->amount;
                $paymentTotal += $leftoverAmount;

                if ($leftoverPayment->purchase_order_payment_id) {
                    $leftoverPurchaseOrderPaymentId = (int) $leftoverPayment->purchase_order_payment_id;
                    $purchaseOrderPaymentAmounts[$leftoverPurchaseOrderPaymentId] = ($purchaseOrderPaymentAmounts[$leftoverPurchaseOrderPaymentId] ?? 0) + $leftoverAmount;
                }

                if ($leftoverPayment->purchase_return_id) {
                    $leftoverPurchaseReturnId = (int) $leftoverPayment->purchase_return_id;
                    $purchaseReturnAmounts[$leftoverPurchaseReturnId] = ($purchaseReturnAmounts[$leftoverPurchaseReturnId] ?? 0) + $leftoverAmount;
                }
            }
        }

        foreach ($purchaseOrderPaymentAmounts as $purchaseOrderPaymentId => $requestedAmount) {
            $purchaseOrderPayment = PurchaseOrderPayment::find($purchaseOrderPaymentId);
            if (! $purchaseOrderPayment) {
                continue;
            }

            $allocatedByOthers = PurchaseInvoicePayment::query()
                ->where('purchase_order_payment_id', $purchaseOrderPaymentId)
                ->when($exceptPurchaseInvoiceId, fn ($query) => $query->where('purchase_invoice_id', '<>', $exceptPurchaseInvoiceId))
                ->sum('amount');

            $available = (float) $purchaseOrderPayment->amount - (float) $allocatedByOthers;

            if ($requestedAmount > $available + 0.00000001) {
                $validator->errors()->add('payments', trans('rules.purchase_invoice.payments_exceed_purchase_order_payment_available'));
            }
        }

        foreach ($purchaseReturnAmounts as $purchaseReturnId => $requestedAmount) {
            $purchaseReturn = PurchaseReturn::find($purchaseReturnId);
            if (! $purchaseReturn) {
                continue;
            }

            $allocatedByOthers = PurchaseInvoicePayment::query()
                ->where('purchase_return_id', $purchaseReturnId)
                ->when($exceptPurchaseInvoiceId, fn ($query) => $query->where('purchase_invoice_id', '<>', $exceptPurchaseInvoiceId))
                ->sum('amount');

            $available = (float) $purchaseReturn->amount_payable
                - (float) $purchaseReturn->amount_received_total
                - (float) $allocatedByOthers;

            if ($requestedAmount > $available + 0.00000001) {
                $validator->errors()->add('payments', trans('rules.purchase_invoice.payments_exceed_purchase_return_available'));
            }
        }

        $expectedAmountPayable = $this->computeExpectedAmountPayable();
        if ($paymentTotal > $expectedAmountPayable + 0.00000001) {
            $validator->errors()->add('payments', trans('rules.purchase_invoice.payments_exceed_amount_payable'));
        }
    }

    /**
     * Item rows of an existing invoice that the payload neither submits nor
     * deletes: they survive the update and still count toward amount_payable.
     * A store request has none.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function leftoverItemInputs(): array
    {
        return [];
    }

    /**
     * Approximation of the invoice amount payable from the raw request inputs
     * (same waterfall as PurchaseInvoiceActions::updateSummary without the
     * per-item residue redistribution, which never changes the header total).
     */
    protected function computeExpectedAmountPayable(): float
    {
        $items = [];
        foreach (array_merge($this->input('items', []), $this->leftoverItemInputs()) as $item) {
            if (! is_array($item)) {
                continue;
            }

            $qty = (float) ($item['qty'] ?? 0);
            $price = (float) ($item['product_unit_price'] ?? 0);
            $priceDiscount = min((float) ($item['price_discount'] ?? 0), $price);
            $subtotal = $qty * max(0, $price - $priceDiscount);
            $subtotalDiscount = min((float) ($item['subtotal_discount'] ?? 0), $subtotal);

            $items[] = [
                'subtotal_after_discount' => max(0, $subtotal - $subtotalDiscount),
                'is_price_include_vat' => (bool) ($item['product_unit_is_price_include_vat'] ?? false),
                'vat_rate' => (float) ($item['vat_rate'] ?? 0),
                'vat_base_numerator' => (int) ($item['vat_base_numerator'] ?? 1),
                'vat_base_denominator' => (int) ($item['vat_base_denominator'] ?? 1),
            ];
        }

        $itemTotalBeforeGlobalDiscount = array_sum(array_column($items, 'subtotal_after_discount'));
        $globalDiscount = (float) $this->input('global_discount', 0);

        $itemTotalAfterVat = 0.0;
        foreach ($items as $item) {
            $subtotalAfterGlobalDiscount = $item['subtotal_after_discount'];
            if ($itemTotalBeforeGlobalDiscount > 0 && $globalDiscount > 0) {
                $subtotalAfterGlobalDiscount = max(
                    0,
                    $subtotalAfterGlobalDiscount - ($subtotalAfterGlobalDiscount / $itemTotalBeforeGlobalDiscount) * $globalDiscount
                );
            }

            $vatRate = $item['vat_rate'];
            $vatBaseFactor = $item['vat_base_denominator'] > 0
                ? $item['vat_base_numerator'] / $item['vat_base_denominator']
                : 0;

            $vatBase = 0.0;
            if ($subtotalAfterGlobalDiscount > 0 && $vatRate > 0 && $vatBaseFactor > 0) {
                $vatBase = $item['is_price_include_vat']
                    ? ($subtotalAfterGlobalDiscount / (1 + ($vatRate / 100))) * $vatBaseFactor
                    : $subtotalAfterGlobalDiscount * $vatBaseFactor;
            }

            $vat = max(0, $vatBase * ($vatRate / 100));

            $itemTotalAfterVat += $item['is_price_include_vat']
                ? $subtotalAfterGlobalDiscount
                : $subtotalAfterGlobalDiscount + $vat;
        }

        return $itemTotalAfterVat + (float) $this->input('rounding', 0);
    }

    public function attributes()
    {
        return array_merge(
            trans('validation_attributes.purchase_invoice'),
            [
                'items.*.purchase_order_item_id' => trans('validation_attributes.purchase_invoice_item.purchase_order_item_id'),
                'items.*.qty' => trans('validation_attributes.purchase_invoice_item.qty'),
                'items.*.product_unit_id' => trans('validation_attributes.purchase_invoice_item.product_unit_id'),
                'items.*.product_unit_conversion_value' => trans('validation_attributes.purchase_invoice_item.product_unit_conversion_value'),
                'items.*.product_unit_price' => trans('validation_attributes.purchase_invoice_item.product_unit_price'),
                'items.*.product_unit_is_price_include_vat' => trans('validation_attributes.purchase_invoice_item.product_unit_is_price_include_vat'),
                'items.*.price_discount' => trans('validation_attributes.purchase_invoice_item.price_discount'),
                'items.*.subtotal_discount' => trans('validation_attributes.purchase_invoice_item.subtotal_discount'),
                'items.*.vat_profile_id' => trans('validation_attributes.purchase_invoice_item.vat_profile_id'),
                'items.*.vat_rate' => trans('validation_attributes.purchase_invoice_item.vat_rate'),
                'items.*.vat_base_numerator' => trans('validation_attributes.purchase_invoice_item.vat_base_numerator'),
                'items.*.vat_base_denominator' => trans('validation_attributes.purchase_invoice_item.vat_base_denominator'),
                'items.*.remarks' => trans('validation_attributes.purchase_invoice_item.remarks'),

                'payments.*.code' => trans('validation_attributes.purchase_invoice_payment.code'),
                'payments.*.date' => trans('validation_attributes.purchase_invoice_payment.date'),
                'payments.*.payment_type' => trans('validation_attributes.purchase_invoice_payment.payment_type'),
                'payments.*.cash_account_id' => trans('validation_attributes.purchase_invoice_payment.cash_account_id'),
                'payments.*.purchase_order_payment_id' => trans('validation_attributes.purchase_invoice_payment.purchase_order_payment_id'),
                'payments.*.purchase_return_id' => trans('validation_attributes.purchase_invoice_payment.purchase_return_id'),
                'payments.*.amount' => trans('validation_attributes.purchase_invoice_payment.amount'),
                'payments.*.remarks' => trans('validation_attributes.purchase_invoice_payment.remarks'),
            ],
        );
    }
}
