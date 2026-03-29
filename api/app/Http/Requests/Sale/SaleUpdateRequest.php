<?php

namespace App\Http\Requests\Sale;

use App\Helpers\HashidsHelper;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidCustomer;
use App\Rules\IsValidWarehouse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SaleUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $sale = $this->route('sale');

        return $user->can('update', $sale);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'code' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'due_days' => ['required', 'integer', 'min:1'],
            'warehouse_id' => ['required', 'integer', 'bail', new IsValidWarehouse($this->company_id, true)],
            'customer_id' => ['required', 'integer', 'bail', new IsValidCustomer($this->company_id)],
            'delivery_note_reference' => ['present', 'nullable', 'string', 'max:255'],
            'tax_invoice_number' => ['present', 'nullable', 'string', 'max:255'],
            'tax_invoice_vat_base' => ['present', 'nullable', 'numeric', 'min:0'],
            'tax_invoice_vat' => ['present', 'nullable', 'numeric', 'min:0'],
            'return_tax_invoice_number' => ['present', 'nullable', 'string', 'max:255'],
            'return_tax_invoice_vat_base' => ['present', 'nullable', 'numeric', 'min:0'],
            'return_tax_invoice_vat' => ['present', 'nullable', 'numeric', 'min:0'],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],
            'is_posted' => ['required', 'boolean'],
            'total' => ['required', 'numeric', 'min:0'],
            'global_discount_rate' => ['required', 'numeric', 'min:0'],
            'global_discount_fixed' => ['required', 'numeric', 'min:0'],
            'additional_cost' => ['required', 'numeric', 'min:0'],
            'rounding' => ['required', 'numeric', 'min:0'],
            'grand_total' => ['required', 'numeric', 'min:0'],
            'return_total' => ['required', 'numeric', 'min:0'],
            'return_global_discount_rate' => ['required', 'numeric', 'min:0'],
            'return_global_discount_fixed' => ['required', 'numeric', 'min:0'],
            'return_rounding' => ['required', 'numeric', 'min:0'],
            'return_grand_total' => ['required', 'numeric', 'min:0'],
            'amount_due' => ['required', 'numeric', 'min:0'],
            'amount_paid_by_sale_order_down_payment' => ['required', 'numeric', 'min:0'],
            'amount_paid_by_sale_return' => ['required', 'numeric', 'min:0'],
            'amount_paid_before_invoice' => ['required', 'numeric', 'min:0'],
            'amount_paid_on_invoice' => ['required', 'numeric', 'min:0'],
            'amount_paid_after_invoice' => ['required', 'numeric', 'min:0'],
            'amount_paid_total' => ['required', 'numeric', 'min:0'],
            'is_paid_off' => ['required', 'boolean'],
            'is_valid' => ['required', 'boolean'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.sale.company'),
            'code' => trans('validation_attributes.sale.code'),
            'remarks' => trans('validation_attributes.sale.remarks'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'warehouse_id' => $this->filled('warehouse_id') ? HashidsHelper::decodeId($this->warehouse_id) : null,
            'customer_id' => $this->filled('customer_id') ? HashidsHelper::decodeId($this->customer_id) : null,
            'delivery_note_reference' => $this->filled('delivery_note_reference') ? $this['delivery_note_reference'] : null,
            'tax_invoice_number' => $this->filled('tax_invoice_number') ? $this['tax_invoice_number'] : null,
            'tax_invoice_vat_base' => $this->filled('tax_invoice_vat_base') ? $this['tax_invoice_vat_base'] : null,
            'tax_invoice_vat' => $this->filled('tax_invoice_vat') ? $this['tax_invoice_vat'] : null,
            'return_tax_invoice_number' => $this->filled('return_tax_invoice_number') ? $this['return_tax_invoice_number'] : null,
            'return_tax_invoice_vat_base' => $this->filled('return_tax_invoice_vat_base') ? $this['return_tax_invoice_vat_base'] : null,
            'return_tax_invoice_vat' => $this->filled('return_tax_invoice_vat') ? $this['return_tax_invoice_vat'] : null,
            'remarks' => $this->filled('remarks') ? $this['remarks'] : null,
        ]);
    }
}
