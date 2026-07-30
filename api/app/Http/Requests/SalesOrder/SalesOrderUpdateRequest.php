<?php

namespace App\Http\Requests\SalesOrder;

use App\Helpers\HashidsHelper;
use App\Models\ProductUnit;
use App\Models\SalesOrder;
use App\Models\SalesOrderPayment;
use App\Models\SalesOrderPaymentRefund;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidCompany;
use App\Rules\IsValidCustomer;
use App\Rules\IsValidDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SalesOrderUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $salesOrder = $this->route('sales_order');

        return $user->can('update', $salesOrder);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', 'bail', new IsValidBranch($this->company_id, true)],
            'code' => ['required', 'string', 'max:255'],
            'date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'due_days' => ['required', 'integer', 'min:0'],
            'customer_id' => ['present', 'nullable', 'integer', 'bail', new ExistsForCompany('customers', $this->company_id), new IsValidCustomer($this->company_id)],
            'remarks' => ['present', 'nullable', 'string'],
            'global_discount' => ['required', 'numeric', 'min:0'],
            'rounding' => ['required', 'numeric'],

            'delete_item_ids' => ['present', 'array'],
            'delete_item_ids.*' => ['required', 'integer', new ExistsForCompany('sales_order_items', $this->company_id)],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('sales_order_items', $this->company_id)],
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

            'delete_payment_ids' => ['present', 'array'],
            'delete_payment_ids.*' => ['required', 'integer', new ExistsForCompany('sales_order_payments', $this->company_id)],
            'payments' => ['present', 'array'],
            'payments.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('sales_order_payments', $this->company_id)],
            'payments.*.code' => ['required', 'string', 'max:255'],
            'payments.*.date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'payments.*.cash_account_id' => ['required', 'integer', 'bail', new ExistsForCompany('cash_accounts', $this->company_id), new IsValidCashAccount($this->branch_id)],
            'payments.*.amount' => ['required', 'numeric', 'min:0'],
            'payments.*.remarks' => ['present', 'nullable', 'string', 'max:255'],

            'delete_refunded_payment_ids' => ['present', 'array'],
            'delete_refunded_payment_ids.*' => ['required', 'integer', new ExistsForCompany('sales_order_payment_refunds', $this->company_id)],
            'refunded_payments' => [
                'present',
                'array',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $paymentsTotal = (float) collect($this->input('payments', []))->sum(function ($row) {
                        return max((float) ($row['amount'] ?? 0), 0);
                    });
                    $refundedPaymentsTotal = (float) collect(is_array($value) ? $value : [])->sum(function ($row) {
                        return max((float) ($row['amount'] ?? 0), 0);
                    });
                    $salesOrder = $this->route('sales_order');
                    $remainingAllocatedPaymentTotal = 0;

                    if ($salesOrder) {
                        $deletePaymentIds = collect($this->input('delete_payment_ids', []))
                            ->filter(fn ($id) => ! is_null($id))
                            ->map(fn ($id) => (int) $id)
                            ->values();

                        $remainingAllocatedPaymentTotal = (float) $salesOrder->payments()
                            ->when(
                                $deletePaymentIds->isNotEmpty(),
                                fn ($query) => $query->whereNotIn('id', $deletePaymentIds->all())
                            )
                            ->get()
                            ->sum('amount_allocated');
                    }

                    $maxRefundableAmount = max($paymentsTotal - $remainingAllocatedPaymentTotal, 0);

                    if ($refundedPaymentsTotal > $maxRefundableAmount) {
                        $fail(trans('rules.sales_order.exceed_available_down_payment'));
                    }
                },
            ],
            'refunded_payments.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('sales_order_payment_refunds', $this->company_id)],
            'refunded_payments.*.code' => ['required', 'string', 'max:255'],
            'refunded_payments.*.date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'refunded_payments.*.cash_account_id' => ['required', 'integer', 'bail', new ExistsForCompany('cash_accounts', $this->company_id), new IsValidCashAccount($this->branch_id)],
            'refunded_payments.*.amount' => ['required', 'numeric', 'min:0'],
            'refunded_payments.*.remarks' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.sales_order.company_id'),
            'branch_id' => trans('validation_attributes.sales_order.branch_id'),
            'code' => trans('validation_attributes.sales_order.code'),
            'date' => trans('validation_attributes.sales_order.date'),
            'due_days' => trans('validation_attributes.sales_order.due_days'),
            'customer_id' => trans('validation_attributes.sales_order.customer_id'),
            'remarks' => trans('validation_attributes.sales_order.remarks'),
            'global_discount' => trans('validation_attributes.sales_order.global_discount'),
            'rounding' => trans('validation_attributes.sales_order.rounding'),

            'items.*.id' => trans('validation_attributes.sales_order_item.id'),
            'items.*.qty' => trans('validation_attributes.sales_order_item.qty'),
            'items.*.product_unit_id' => trans('validation_attributes.sales_order_item.product_unit_id'),
            'items.*.product_unit_conversion_value' => trans('validation_attributes.sales_order_item.product_unit_conversion_value'),
            'items.*.product_unit_price' => trans('validation_attributes.sales_order_item.product_unit_price'),
            'items.*.product_unit_is_price_include_vat' => trans('validation_attributes.sales_order_item.product_unit_is_price_include_vat'),
            'items.*.price_discount' => trans('validation_attributes.sales_order_item.price_discount'),
            'items.*.subtotal_discount' => trans('validation_attributes.sales_order_item.subtotal_discount'),
            'items.*.vat_profile_id' => trans('validation_attributes.sales_order_item.vat_profile_id'),
            'items.*.vat_rate' => trans('validation_attributes.sales_order_item.vat_rate'),
            'items.*.vat_base_numerator' => trans('validation_attributes.sales_order_item.vat_base_numerator'),
            'items.*.vat_base_denominator' => trans('validation_attributes.sales_order_item.vat_base_denominator'),
            'items.*.remarks' => trans('validation_attributes.sales_order_item.remarks'),

            'payments.*.id' => trans('validation_attributes.sales_order_payment.id'),
            'payments.*.code' => trans('validation_attributes.sales_order_payment.code'),
            'payments.*.date' => trans('validation_attributes.sales_order_payment.date'),
            'payments.*.cash_account_id' => trans('validation_attributes.sales_order_payment.cash_account_id'),
            'payments.*.amount' => trans('validation_attributes.sales_order_payment.amount'),
            'payments.*.remarks' => trans('validation_attributes.sales_order_payment.remarks'),

            'refunded_payments.*.id' => trans('validation_attributes.sales_order_payment_refund.id'),
            'refunded_payments.*.code' => trans('validation_attributes.sales_order_payment_refund.code'),
            'refunded_payments.*.date' => trans('validation_attributes.sales_order_payment_refund.date'),
            'refunded_payments.*.cash_account_id' => trans('validation_attributes.sales_order_payment_refund.cash_account_id'),
            'refunded_payments.*.amount' => trans('validation_attributes.sales_order_payment_refund.amount'),
            'refunded_payments.*.remarks' => trans('validation_attributes.sales_order_payment_refund.remarks'),
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);
            $productUnitIds = collect($items)
                ->pluck('product_unit_id')
                ->filter(fn ($productUnitId) => is_numeric($productUnitId))
                ->map(fn ($productUnitId) => (int) $productUnitId)
                ->unique()
                ->values();

            if ($productUnitIds->isNotEmpty()) {
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
                        $validator->errors()->add("items.$index.product_unit_id", trans('rules.sales_order.duplicate_product'));

                        continue;
                    }

                    $seenProductIds[$productId] = true;
                }
            }

            $salesOrder = $this->route('sales_order');
            $code = $this->input('code');

            if (
                ! empty($code)
                && $code !== config('dcslab.KEYWORDS.AUTO')
                && SalesOrder::where('company_id', $this->company_id)
                    ->where('code', $code)
                    ->where('id', '<>', $salesOrder?->id)
                    ->exists()
            ) {
                $validator->errors()->add('code', trans('rules.unique_code'));
            }

            $salesOrderItemIds = $salesOrder?->items()->pluck('id')->all() ?? [];
            $salesOrderPayments = $salesOrder?->payments()->get()->keyBy('id') ?? collect();
            $salesOrderPaymentIds = $salesOrderPayments->keys()->all();
            $salesOrderRefundedPaymentIds = $salesOrder?->refundedPayments()->pluck('id')->all() ?? [];

            foreach ($this->input('delete_item_ids', []) as $index => $deleteItemId) {
                if (! in_array($deleteItemId, $salesOrderItemIds, true)) {
                    $validator->errors()->add(
                        "delete_item_ids.$index",
                        trans('rules.sales_order.invalid_delete_item_reference')
                    );
                }
            }

            foreach ($this->input('items', []) as $index => $item) {
                $itemId = $item['id'] ?? null;

                if (! is_null($itemId) && ! in_array($itemId, $salesOrderItemIds, true)) {
                    $validator->errors()->add(
                        "items.$index.id",
                        trans('rules.sales_order.invalid_item_reference')
                    );
                }
            }

            foreach ($this->input('delete_payment_ids', []) as $index => $paymentId) {
                if (! in_array($paymentId, $salesOrderPaymentIds, true)) {
                    $validator->errors()->add(
                        "delete_payment_ids.$index",
                        trans('rules.sales_order.invalid_payment_reference')
                    );

                    continue;
                }

                $salesOrderPayment = $salesOrderPayments->get($paymentId);
                if ($salesOrderPayment && (float) $salesOrderPayment->amount_allocated > 0) {
                    $validator->errors()->add(
                        "delete_payment_ids.$index",
                        trans('rules.sales_order.payment_already_allocated')
                    );
                }
            }

            foreach ($this->input('payments', []) as $index => $payment) {
                $paymentId = $payment['id'] ?? null;

                if (! is_null($paymentId) && ! in_array($paymentId, $salesOrderPaymentIds, true)) {
                    $validator->errors()->add(
                        "payments.$index.id",
                        trans('rules.sales_order.invalid_payment_reference')
                    );

                    continue;
                }

                if (! is_null($paymentId)) {
                    $salesOrderPayment = $salesOrderPayments->get($paymentId);
                    $amount = (float) ($payment['amount'] ?? 0);

                    if ($salesOrderPayment && $amount < (float) $salesOrderPayment->amount_allocated) {
                        $validator->errors()->add(
                            "payments.$index.amount",
                            trans('rules.sales_order.payment_already_allocated')
                        );
                    }
                }
            }

            foreach ($this->input('delete_refunded_payment_ids', []) as $index => $refundedPaymentId) {
                if (! in_array($refundedPaymentId, $salesOrderRefundedPaymentIds, true)) {
                    $validator->errors()->add(
                        "delete_refunded_payment_ids.$index",
                        trans('rules.sales_order.invalid_refunded_payment_reference')
                    );
                }
            }

            foreach ($this->input('refunded_payments', []) as $index => $refundedPayment) {
                $refundedPaymentId = $refundedPayment['id'] ?? null;

                if (! is_null($refundedPaymentId) && ! in_array($refundedPaymentId, $salesOrderRefundedPaymentIds, true)) {
                    $validator->errors()->add(
                        "refunded_payments.$index.id",
                        trans('rules.sales_order.invalid_refunded_payment_reference')
                    );
                }
            }

            $paymentCodesInRequest = [];
            foreach ($this->input('payments', []) as $index => $payment) {
                $paymentCode = $payment['code'] ?? null;
                $paymentId = $payment['id'] ?? null;

                if (empty($paymentCode) || $paymentCode === config('dcslab.KEYWORDS.AUTO')) {
                    continue;
                }

                if (in_array($paymentCode, $paymentCodesInRequest, true)) {
                    $validator->errors()->add("payments.$index.code", trans('rules.unique_code'));

                    continue;
                }

                $paymentCodesInRequest[] = $paymentCode;

                $paymentQuery = SalesOrderPayment::where('company_id', $this->company_id)
                    ->where('code', $paymentCode);

                if (! empty($paymentId)) {
                    $paymentQuery->where('id', '<>', $paymentId);
                }

                if ($paymentQuery->exists()) {
                    $validator->errors()->add("payments.$index.code", trans('rules.unique_code'));
                }
            }

            $refundedPaymentCodesInRequest = [];
            foreach ($this->input('refunded_payments', []) as $index => $refundedPayment) {
                $refundedPaymentCode = $refundedPayment['code'] ?? null;
                $refundedPaymentId = $refundedPayment['id'] ?? null;

                if (empty($refundedPaymentCode) || $refundedPaymentCode === config('dcslab.KEYWORDS.AUTO')) {
                    continue;
                }

                if (in_array($refundedPaymentCode, $refundedPaymentCodesInRequest, true)) {
                    $validator->errors()->add("refunded_payments.$index.code", trans('rules.unique_code'));

                    continue;
                }

                $refundedPaymentCodesInRequest[] = $refundedPaymentCode;

                $refundedPaymentQuery = SalesOrderPaymentRefund::where('company_id', $this->company_id)
                    ->where('code', $refundedPaymentCode);

                if (! empty($refundedPaymentId)) {
                    $refundedPaymentQuery->where('id', '<>', $refundedPaymentId);
                }

                if ($refundedPaymentQuery->exists()) {
                    $validator->errors()->add("refunded_payments.$index.code", trans('rules.unique_code'));
                }
            }
        });
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->route('sales_order')?->company_id ?? null,
            'branch_id' => $this->route('sales_order')?->branch_id ?? null,
            'customer_id' => $this->filled('customer_id') ? HashidsHelper::decodeId($this->customer_id) : null,
        ]);

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

        if (is_array($this->input('payments'))) {
            $payments = [];
            foreach ($this->input('payments') as $payment) {
                if (array_key_exists('id', $payment) && ! is_null($payment['id'])) {
                    $payment['id'] = HashidsHelper::decodeId($payment['id']);
                }
                if (array_key_exists('cash_account_id', $payment) && ! is_null($payment['cash_account_id'])) {
                    $payment['cash_account_id'] = HashidsHelper::decodeId($payment['cash_account_id']);
                }
                $payments[] = $payment;
            }
            $this->merge(['payments' => $payments]);
        }

        if (is_array($this->input('delete_payment_ids'))) {
            $deletePaymentIds = [];
            foreach ($this->input('delete_payment_ids') as $id) {
                $deletePaymentIds[] = HashidsHelper::decodeId($id);
            }
            $this->merge(['delete_payment_ids' => $deletePaymentIds]);
        }

        if (is_array($this->input('refunded_payments'))) {
            $refundedPayments = [];
            foreach ($this->input('refunded_payments') as $refundedPayment) {
                if (array_key_exists('id', $refundedPayment) && ! is_null($refundedPayment['id'])) {
                    $refundedPayment['id'] = HashidsHelper::decodeId($refundedPayment['id']);
                }
                if (array_key_exists('cash_account_id', $refundedPayment) && ! is_null($refundedPayment['cash_account_id'])) {
                    $refundedPayment['cash_account_id'] = HashidsHelper::decodeId($refundedPayment['cash_account_id']);
                }
                $refundedPayments[] = $refundedPayment;
            }
            $this->merge(['refunded_payments' => $refundedPayments]);
        }

        if (is_array($this->input('delete_refunded_payment_ids'))) {
            $deleteRefundedPaymentIds = [];
            foreach ($this->input('delete_refunded_payment_ids') as $id) {
                $deleteRefundedPaymentIds[] = HashidsHelper::decodeId($id);
            }
            $this->merge(['delete_refunded_payment_ids' => $deleteRefundedPaymentIds]);
        }
    }
}
