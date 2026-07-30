<?php

namespace App\Http\Requests\PurchaseInvoice;

use App\Enums\PaymentTypeEnum;
use App\Helpers\HashidsHelper;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidDate;
use App\Rules\IsValidSupplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PurchaseInvoiceUpdateRequest extends PurchaseInvoiceStoreRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $purchaseInvoice = $this->route('purchase_invoice');

        return $user->can('update', $purchaseInvoice);
    }

    public function prepareForValidation()
    {
        $purchaseInvoice = $this->route('purchase_invoice');

        $this->merge([
            'company_id' => $purchaseInvoice->company_id,
            'branch_id' => $purchaseInvoice->branch_id,
            'supplier_id' => $this->filled('supplier_id') ? HashidsHelper::decodeId($this->supplier_id) : null,
            'purchase_order_id' => $this->filled('purchase_order_id') ? HashidsHelper::decodeId($this->purchase_order_id) : null,
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
                    foreach (['id', 'product_unit_id', 'vat_profile_id', 'purchase_order_item_id'] as $key) {
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
                    foreach (['id', 'cash_account_id', 'purchase_order_payment_id', 'purchase_return_id'] as $key) {
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
        $purchaseInvoice = $this->route('purchase_invoice');

        return [
            'company_id' => ['required', 'integer'],
            'branch_id' => ['required', 'integer'],
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

            'delete_item_ids' => ['present', 'array'],
            'delete_item_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('purchase_invoice_items', 'id')->where(function ($query) use ($purchaseInvoice) {
                    $query->where('company_id', $this->company_id)
                        ->where('purchase_invoice_id', $purchaseInvoice->id)
                        ->whereNull('deleted_at');
                }),
            ],

            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => [
                'present',
                'nullable',
                'integer',
                Rule::exists('purchase_invoice_items', 'id')->where(function ($query) use ($purchaseInvoice) {
                    $query->where('company_id', $this->company_id)
                        ->where('purchase_invoice_id', $purchaseInvoice->id)
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
            'items.*.purchase_order_item_id' => [
                'present',
                'nullable',
                'integer',
                new ExistsForCompany('purchase_order_items', $this->company_id),
            ],
            'items.*.remarks' => ['present', 'nullable', 'string', 'max:255'],

            'delete_payment_ids' => ['present', 'array'],
            'delete_payment_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('purchase_invoice_payments', 'id')->where(function ($query) use ($purchaseInvoice) {
                    $query->where('company_id', $this->company_id)
                        ->where('purchase_invoice_id', $purchaseInvoice->id)
                        ->whereNull('deleted_at');
                }),
            ],

            'payments' => ['present', 'array'],
            'payments.*.id' => [
                'present',
                'nullable',
                'integer',
                Rule::exists('purchase_invoice_payments', 'id')->where(function ($query) use ($purchaseInvoice) {
                    $query->where('company_id', $this->company_id)
                        ->where('purchase_invoice_id', $purchaseInvoice->id)
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
            $purchaseInvoice = $this->route('purchase_invoice');

            $this->validateDuplicateProducts($validator);
            $this->validatePurchaseOrderLinkage($validator);
            $this->validatePayments($validator, exceptPurchaseInvoiceId: $purchaseInvoice->id);
        });
    }

    protected function leftoverItemInputs(): array
    {
        $purchaseInvoice = $this->route('purchase_invoice');
        if (! $purchaseInvoice) {
            return [];
        }

        $handledItemIds = collect($this->input('items', []))
            ->pluck('id')
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->merge(
                collect($this->input('delete_item_ids', []))
                    ->filter(fn ($id) => is_numeric($id))
                    ->map(fn ($id) => (int) $id)
            )
            ->unique()
            ->values();

        return $purchaseInvoice->items()
            ->when($handledItemIds->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $handledItemIds->all()))
            ->get()
            ->map(fn ($item) => [
                'qty' => (float) $item->qty,
                'product_unit_price' => (float) $item->product_unit_price,
                'price_discount' => (float) $item->price_discount,
                'subtotal_discount' => (float) $item->subtotal_discount,
                'product_unit_is_price_include_vat' => (bool) $item->product_unit_is_price_include_vat,
                'vat_rate' => (float) $item->vat_rate,
                'vat_base_numerator' => (int) $item->vat_base_numerator,
                'vat_base_denominator' => (int) $item->vat_base_denominator,
            ])
            ->all();
    }

    public function attributes()
    {
        return array_merge(
            parent::attributes(),
            [
                'items.*.id' => trans('validation_attributes.purchase_invoice_item.id'),
                'payments.*.id' => trans('validation_attributes.purchase_invoice_payment.id'),
            ],
        );
    }

    public function messages()
    {
        return [
            'delete_item_ids.*.exists' => trans('rules.purchase_invoice.invalid_delete_item_reference'),
            'items.*.id.exists' => trans('rules.purchase_invoice.invalid_item_reference'),
            'delete_payment_ids.*.exists' => trans('rules.purchase_invoice.invalid_delete_payment_reference'),
            'payments.*.id.exists' => trans('rules.purchase_invoice.invalid_payment_reference'),
        ];
    }
}
