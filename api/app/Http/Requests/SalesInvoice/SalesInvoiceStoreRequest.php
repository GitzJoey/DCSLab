<?php

namespace App\Http\Requests\SalesInvoice;

use App\Enums\PaymentTypeEnum;
use App\Helpers\HashidsHelper;
use App\Models\ProductUnit;
use App\Models\SalesInvoice;
use App\Models\SalesInvoicePayment;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\SalesOrderPayment;
use App\Models\SalesReturn;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidCompany;
use App\Rules\IsValidCustomer;
use App\Rules\IsValidDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SalesInvoiceStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', SalesInvoice::class);
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'customer_id' => $this->filled('customer_id') ? HashidsHelper::decodeId($this->customer_id) : null,
            'sales_order_id' => $this->filled('sales_order_id') ? HashidsHelper::decodeId($this->sales_order_id) : null,
            'tax_invoice_number' => $this->filled('tax_invoice_number') ? $this['tax_invoice_number'] : null,
            'remarks' => $this->filled('remarks') ? $this['remarks'] : null,
        ]);

        if (is_array($this->input('items'))) {
            $items = [];
            foreach ($this->input('items') as $item) {
                if (is_array($item)) {
                    foreach (['product_unit_id', 'vat_profile_id', 'sales_order_item_id'] as $key) {
                        if (array_key_exists($key, $item) && ! is_null($item[$key])) {
                            $item[$key] = HashidsHelper::decodeId($item[$key]);
                        }
                    }

                    $item['sales_order_item_id'] = $item['sales_order_item_id'] ?? null;
                    $item['vat_profile_id'] = $item['vat_profile_id'] ?? null;
                    $item['remarks'] = $item['remarks'] ?? null;
                }

                $items[] = $item;
            }

            $this->merge(['items' => $items]);
        }

        if (is_array($this->input('payments'))) {
            $payments = [];
            foreach ($this->input('payments') as $payment) {
                if (is_array($payment)) {
                    foreach (['cash_account_id', 'sales_order_payment_id', 'sales_return_id'] as $key) {
                        if (array_key_exists($key, $payment) && ! is_null($payment[$key])) {
                            $payment[$key] = HashidsHelper::decodeId($payment[$key]);
                        }
                    }

                    $payment['cash_account_id'] = $payment['cash_account_id'] ?? null;
                    $payment['sales_order_payment_id'] = $payment['sales_order_payment_id'] ?? null;
                    $payment['sales_return_id'] = $payment['sales_return_id'] ?? null;
                    $payment['remarks'] = $payment['remarks'] ?? null;
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
            'customer_id' => [
                'required',
                'integer',
                'bail',
                new ExistsForCompany('customers', $this->company_id),
                new IsValidCustomer($this->company_id),
            ],
            'sales_order_id' => ['required', 'integer', new ExistsForCompany('sales_orders', $this->company_id)],
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
            'items.*.sales_order_item_id' => [
                'present',
                'nullable',
                'integer',
                new ExistsForCompany('sales_order_items', $this->company_id),
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
            'payments.*.sales_order_payment_id' => [
                'present',
                'nullable',
                'prohibited_unless:payments.*.payment_type,down_payment',
                'integer',
                new ExistsForCompany('sales_order_payments', $this->company_id),
            ],
            'payments.*.sales_return_id' => [
                'present',
                'nullable',
                'prohibited_unless:payments.*.payment_type,return',
                'integer',
                new ExistsForCompany('sales_returns', $this->company_id),
            ],
            'payments.*.amount' => ['required', 'numeric', 'gt:0'],
            'payments.*.remarks' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateDuplicateProducts($validator);
            $this->validateSalesOrderLinkage($validator);
            $this->validatePayments($validator, exceptSalesInvoiceId: null);
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
                $validator->errors()->add("items.$index.product_unit_id", trans('rules.sales_invoice.duplicate_product'));

                continue;
            }

            $seenProductIds[$productId] = true;
        }
    }

    protected function validateSalesOrderLinkage($validator): void
    {
        $salesOrderId = $this->input('sales_order_id');
        if (! is_numeric($salesOrderId)) {
            return;
        }

        $salesOrder = SalesOrder::find((int) $salesOrderId);
        if (is_null($salesOrder)) {
            return;
        }

        if (is_null($salesOrder->customer_id)) {
            $validator->errors()->add('sales_order_id', trans('rules.sales_invoice.sales_order_must_have_customer'));

            return;
        }

        if ($this->filled('customer_id') && (int) $this->input('customer_id') !== (int) $salesOrder->customer_id) {
            $validator->errors()->add('sales_order_id', trans('rules.sales_invoice.sales_order_customer_must_match'));
        }

        if ($this->filled('branch_id') && (int) $this->input('branch_id') !== (int) $salesOrder->branch_id) {
            $validator->errors()->add('sales_order_id', trans('rules.sales_invoice.sales_order_branch_must_match'));
        }

        $salesOrderItemIds = SalesOrderItem::query()
            ->where('sales_order_id', $salesOrder->id)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($this->input('items', []) as $index => $item) {
            $salesOrderItemId = $item['sales_order_item_id'] ?? null;
            if (! is_numeric($salesOrderItemId)) {
                continue;
            }

            if (! in_array((int) $salesOrderItemId, $salesOrderItemIds, true)) {
                $validator->errors()->add(
                    "items.$index.sales_order_item_id",
                    trans('rules.sales_invoice.invalid_sales_order_item_reference')
                );
            }
        }
    }

    protected function validatePayments($validator, ?int $exceptSalesInvoiceId): void
    {
        $payments = $this->input('payments', []);
        $salesOrderId = is_numeric($this->input('sales_order_id')) ? (int) $this->input('sales_order_id') : null;
        $customerId = is_numeric($this->input('customer_id')) ? (int) $this->input('customer_id') : null;

        $salesOrderPaymentAmounts = [];
        $salesReturnAmounts = [];
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

                    $codeQuery = SalesInvoicePayment::query()
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
                    'attribute' => trans('validation_attributes.sales_invoice_payment.cash_account_id'),
                ]));
            }

            if ($paymentType === PaymentTypeEnum::DOWN_PAYMENT) {
                if (empty($payment['sales_order_payment_id'])) {
                    $validator->errors()->add("payments.$index.sales_order_payment_id", trans('validation.required', [
                        'attribute' => trans('validation_attributes.sales_invoice_payment.sales_order_payment_id'),
                    ]));
                } else {
                    $salesOrderPaymentId = (int) $payment['sales_order_payment_id'];
                    $salesOrderPayment = SalesOrderPayment::find($salesOrderPaymentId);

                    if ($salesOrderPayment) {
                        if (! is_null($salesOrderId) && (int) $salesOrderPayment->sales_order_id !== $salesOrderId) {
                            $validator->errors()->add(
                                "payments.$index.sales_order_payment_id",
                                trans('rules.sales_invoice.sales_order_payment_must_match_sales_order')
                            );
                        }

                        $salesOrderPaymentAmounts[$salesOrderPaymentId] = ($salesOrderPaymentAmounts[$salesOrderPaymentId] ?? 0) + $amount;
                    }
                }
            }

            if ($paymentType === PaymentTypeEnum::RETURN) {
                if (empty($payment['sales_return_id'])) {
                    $validator->errors()->add("payments.$index.sales_return_id", trans('validation.required', [
                        'attribute' => trans('validation_attributes.sales_invoice_payment.sales_return_id'),
                    ]));
                } else {
                    $salesReturnId = (int) $payment['sales_return_id'];
                    $salesReturn = SalesReturn::find($salesReturnId);

                    if ($salesReturn) {
                        if (! is_null($customerId) && (int) $salesReturn->customer_id !== $customerId) {
                            $validator->errors()->add(
                                "payments.$index.sales_return_id",
                                trans('rules.sales_invoice.sales_return_customer_must_match')
                            );
                        }

                        if (is_null($salesReturn->sales_invoice_id)) {
                            $validator->errors()->add(
                                "payments.$index.sales_return_id",
                                trans('rules.sales_invoice.sales_return_must_be_invoiced')
                            );
                        }

                        $salesReturnAmounts[$salesReturnId] = ($salesReturnAmounts[$salesReturnId] ?? 0) + $amount;
                    }
                }
            }
        }

        // On update, surviving rows of this invoice that are neither present in
        // the payments input nor listed for deletion keep counting toward the
        // caps (allocatedByOthers below excludes ALL rows of this invoice).
        if ($exceptSalesInvoiceId) {
            $inputPaymentIds = collect($payments)
                ->pluck('id')
                ->filter(fn ($id) => is_numeric($id))
                ->map(fn ($id) => (int) $id)
                ->all();
            $deletePaymentIds = collect($this->input('delete_payment_ids', []))
                ->filter(fn ($id) => is_numeric($id))
                ->map(fn ($id) => (int) $id)
                ->all();

            $leftoverPayments = SalesInvoicePayment::query()
                ->where('sales_invoice_id', $exceptSalesInvoiceId)
                ->whereNotIn('id', array_merge($inputPaymentIds, $deletePaymentIds))
                ->get();

            foreach ($leftoverPayments as $leftoverPayment) {
                $leftoverAmount = (float) $leftoverPayment->amount;
                $paymentTotal += $leftoverAmount;

                if ($leftoverPayment->sales_order_payment_id) {
                    $leftoverSalesOrderPaymentId = (int) $leftoverPayment->sales_order_payment_id;
                    $salesOrderPaymentAmounts[$leftoverSalesOrderPaymentId] = ($salesOrderPaymentAmounts[$leftoverSalesOrderPaymentId] ?? 0) + $leftoverAmount;
                }

                if ($leftoverPayment->sales_return_id) {
                    $leftoverSalesReturnId = (int) $leftoverPayment->sales_return_id;
                    $salesReturnAmounts[$leftoverSalesReturnId] = ($salesReturnAmounts[$leftoverSalesReturnId] ?? 0) + $leftoverAmount;
                }
            }
        }

        foreach ($salesOrderPaymentAmounts as $salesOrderPaymentId => $requestedAmount) {
            $salesOrderPayment = SalesOrderPayment::find($salesOrderPaymentId);
            if (! $salesOrderPayment) {
                continue;
            }

            $allocatedByOthers = SalesInvoicePayment::query()
                ->where('sales_order_payment_id', $salesOrderPaymentId)
                ->when($exceptSalesInvoiceId, fn ($query) => $query->where('sales_invoice_id', '<>', $exceptSalesInvoiceId))
                ->sum('amount');

            $available = (float) $salesOrderPayment->amount - (float) $allocatedByOthers;

            if ($requestedAmount > $available + 0.00000001) {
                $validator->errors()->add('payments', trans('rules.sales_invoice.payments_exceed_sales_order_payment_available'));
            }
        }

        foreach ($salesReturnAmounts as $salesReturnId => $requestedAmount) {
            $salesReturn = SalesReturn::find($salesReturnId);
            if (! $salesReturn) {
                continue;
            }

            $allocatedByOthers = SalesInvoicePayment::query()
                ->where('sales_return_id', $salesReturnId)
                ->when($exceptSalesInvoiceId, fn ($query) => $query->where('sales_invoice_id', '<>', $exceptSalesInvoiceId))
                ->sum('amount');

            $available = (float) $salesReturn->amount_payable
                - (float) $salesReturn->amount_received_total
                - (float) $allocatedByOthers;

            if ($requestedAmount > $available + 0.00000001) {
                $validator->errors()->add('payments', trans('rules.sales_invoice.payments_exceed_sales_return_available'));
            }
        }

        $expectedAmountPayable = $this->computeExpectedAmountPayable();
        if ($paymentTotal > $expectedAmountPayable + 0.00000001) {
            $validator->errors()->add('payments', trans('rules.sales_invoice.payments_exceed_amount_payable'));
        }
    }

    /**
     * Approximation of the invoice amount payable from the raw request inputs
     * (same waterfall as SalesInvoiceActions::updateSummary without the
     * per-item residue redistribution, which never changes the header total).
     */
    protected function computeExpectedAmountPayable(): float
    {
        $items = [];
        foreach ($this->input('items', []) as $item) {
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
            trans('validation_attributes.sales_invoice'),
            [
                'items.*.qty' => trans('validation_attributes.sales_invoice_item.qty'),
                'items.*.product_unit_id' => trans('validation_attributes.sales_invoice_item.product_unit_id'),
                'items.*.product_unit_conversion_value' => trans('validation_attributes.sales_invoice_item.product_unit_conversion_value'),
                'items.*.product_unit_price' => trans('validation_attributes.sales_invoice_item.product_unit_price'),
                'items.*.product_unit_is_price_include_vat' => trans('validation_attributes.sales_invoice_item.product_unit_is_price_include_vat'),
                'items.*.price_discount' => trans('validation_attributes.sales_invoice_item.price_discount'),
                'items.*.subtotal_discount' => trans('validation_attributes.sales_invoice_item.subtotal_discount'),
                'items.*.vat_profile_id' => trans('validation_attributes.sales_invoice_item.vat_profile_id'),
                'items.*.vat_rate' => trans('validation_attributes.sales_invoice_item.vat_rate'),
                'items.*.vat_base_numerator' => trans('validation_attributes.sales_invoice_item.vat_base_numerator'),
                'items.*.vat_base_denominator' => trans('validation_attributes.sales_invoice_item.vat_base_denominator'),
                'items.*.sales_order_item_id' => trans('validation_attributes.sales_invoice_item.sales_order_item_id'),
                'items.*.remarks' => trans('validation_attributes.sales_invoice_item.remarks'),

                'payments.*.code' => trans('validation_attributes.sales_invoice_payment.code'),
                'payments.*.date' => trans('validation_attributes.sales_invoice_payment.date'),
                'payments.*.payment_type' => trans('validation_attributes.sales_invoice_payment.payment_type'),
                'payments.*.cash_account_id' => trans('validation_attributes.sales_invoice_payment.cash_account_id'),
                'payments.*.sales_order_payment_id' => trans('validation_attributes.sales_invoice_payment.sales_order_payment_id'),
                'payments.*.sales_return_id' => trans('validation_attributes.sales_invoice_payment.sales_return_id'),
                'payments.*.amount' => trans('validation_attributes.sales_invoice_payment.amount'),
                'payments.*.remarks' => trans('validation_attributes.sales_invoice_payment.remarks'),
            ],
        );
    }
}
