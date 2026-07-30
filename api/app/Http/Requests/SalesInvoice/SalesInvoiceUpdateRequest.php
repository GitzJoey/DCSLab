<?php

namespace App\Http\Requests\SalesInvoice;

use App\Enums\PaymentTypeEnum;
use App\Helpers\HashidsHelper;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidCustomer;
use App\Rules\IsValidDate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SalesInvoiceUpdateRequest extends SalesInvoiceStoreRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $salesInvoice = $this->route('sales_invoice');

        return $user->can('update', $salesInvoice);
    }

    public function prepareForValidation()
    {
        $salesInvoice = $this->route('sales_invoice');

        $this->merge([
            'company_id' => $salesInvoice->company_id,
            'branch_id' => $salesInvoice->branch_id,
            'customer_id' => $this->filled('customer_id') ? HashidsHelper::decodeId($this->customer_id) : null,
            'sales_order_id' => $this->filled('sales_order_id') ? HashidsHelper::decodeId($this->sales_order_id) : null,
            'delete_item_ids' => collect($this->input('delete_item_ids', []))
                ->map(fn ($id) => HashidsHelper::decodeId($id))
                ->all(),
            'delete_payment_ids' => collect($this->input('delete_payment_ids', []))
                ->map(fn ($id) => HashidsHelper::decodeId($id))
                ->all(),
        ]);

        if (is_array($this->input('items'))) {
            $items = [];
            foreach ($this->input('items') as $item) {
                if (is_array($item)) {
                    foreach (['id', 'product_unit_id', 'vat_profile_id', 'sales_order_item_id'] as $key) {
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
                    foreach (['id', 'cash_account_id', 'sales_order_payment_id', 'sales_return_id'] as $key) {
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
        $salesInvoice = $this->route('sales_invoice');

        return [
            'company_id' => ['required', 'integer'],
            'branch_id' => ['required', 'integer'],
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

            'delete_item_ids' => ['present', 'array'],
            'delete_item_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('sales_invoice_items', 'id')->where(function ($query) use ($salesInvoice) {
                    $query->where('company_id', $this->company_id)
                        ->where('sales_invoice_id', $salesInvoice->id)
                        ->whereNull('deleted_at');
                }),
            ],

            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => [
                'present',
                'nullable',
                'integer',
                Rule::exists('sales_invoice_items', 'id')->where(function ($query) use ($salesInvoice) {
                    $query->where('company_id', $this->company_id)
                        ->where('sales_invoice_id', $salesInvoice->id)
                        ->whereNull('deleted_at');
                }),
            ],
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

            'delete_payment_ids' => ['present', 'array'],
            'delete_payment_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('sales_invoice_payments', 'id')->where(function ($query) use ($salesInvoice) {
                    $query->where('company_id', $this->company_id)
                        ->where('sales_invoice_id', $salesInvoice->id)
                        ->whereNull('deleted_at');
                }),
            ],

            'payments' => ['present', 'array'],
            'payments.*.id' => [
                'present',
                'nullable',
                'integer',
                Rule::exists('sales_invoice_payments', 'id')->where(function ($query) use ($salesInvoice) {
                    $query->where('company_id', $this->company_id)
                        ->where('sales_invoice_id', $salesInvoice->id)
                        ->whereNull('deleted_at');
                }),
            ],
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
            $salesInvoice = $this->route('sales_invoice');

            $this->validateDuplicateProducts($validator);
            $this->validateSalesOrderLinkage($validator);
            $this->validatePayments($validator, exceptSalesInvoiceId: $salesInvoice->id);
        });
    }

    public function attributes()
    {
        return array_merge(
            parent::attributes(),
            [
                'items.*.id' => trans('validation_attributes.sales_invoice_item.id'),
                'payments.*.id' => trans('validation_attributes.sales_invoice_payment.id'),
            ],
        );
    }

    public function messages()
    {
        return [
            'delete_item_ids.*.exists' => trans('rules.sales_invoice.invalid_delete_item_reference'),
            'items.*.id.exists' => trans('rules.sales_invoice.invalid_item_reference'),
            'delete_payment_ids.*.exists' => trans('rules.sales_invoice.invalid_delete_payment_reference'),
            'payments.*.id.exists' => trans('rules.sales_invoice.invalid_payment_reference'),
        ];
    }
}
