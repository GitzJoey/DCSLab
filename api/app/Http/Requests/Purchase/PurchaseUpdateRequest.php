<?php

namespace App\Http\Requests\Purchase;

use App\Helpers\HashidsHelper;
use App\Models\Purchase;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidPurchaseOrder;
use App\Rules\IsValidSupplier;
use App\Rules\IsValidWarehouse;
use App\Rules\PurchaseUpdateValidCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $purchase = $this->route('purchase');

        return $user->can('update', Purchase::class, $purchase) ? true : false;
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'code' => ['required', 'string', 'max:255', new PurchaseUpdateValidCode($this->company_id, $this->route('purchase'))],
            'date' => ['required', 'date'],
            'due_days' => ['required', 'integer', 'min:0'],
            'warehouse_id' => ['nullable', 'integer', new IsValidWarehouse($this->company_id, false)],
            'supplier_id' => ['nullable', 'integer', new IsValidSupplier()],
            'purchase_order_id' => ['nullable', 'integer', new IsValidPurchaseOrder($this->company_id)],
            'delivery_note_reference' => ['required', 'string', 'max:255'],
            'purchase_tax_invoice_number' => ['required', 'string', 'max:255'],
            'purchase_tax_invoice_vat_base' => ['required', 'numeric', 'min:0'],
            'purchase_tax_invoice_vat' => ['required', 'numeric', 'min:0'],
            'return_tax_invoice_number' => ['required', 'string', 'max:255'],
            'return_tax_invoice_vat_base' => ['required', 'numeric', 'min:0'],
            'return_tax_invoice_vat' => ['required', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:255'],
            'is_posted' => ['required', 'boolean'],
            'purchase_total' => ['required', 'numeric', 'min:0'],
            'purchase_global_discount_rate' => ['required', 'numeric', 'min:0'],
            'purchase_global_discount_fixed' => ['required', 'numeric', 'min:0'],
            'purchase_additional_cost' => ['required', 'numeric', 'min:0'],
            'purchase_rounding' => ['required', 'numeric', 'min:0'],
            'purchase_grand_total' => ['required', 'numeric', 'min:0'],
            'return_total' => ['required', 'numeric', 'min:0'],
            'return_global_discount_rate' => ['required', 'numeric', 'min:0'],
            'return_global_discount_fixed' => ['required', 'numeric', 'min:0'],
            'return_rounding' => ['required', 'numeric', 'min:0'],
            'return_grand_total' => ['required', 'numeric', 'min:0'],
            'amount_due' => ['required', 'numeric', 'min:0'],
            'amount_paid_by_purchase_order_down_payment' => ['required', 'numeric', 'min:0'],
            'amount_paid_by_purchase_return' => ['required', 'numeric', 'min:0'],
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
            'company_id' => trans('validation_attributes.purchase.company'),
            'branch_id' => trans('validation_attributes.purchase.branch'),
            'code' => trans('validation_attributes.purchase.code'),
            'date' => trans('validation_attributes.purchase.date'),
            'warehouse_id' => trans('validation_attributes.purchase.warehouse'),
            'supplier_id' => trans('validation_attributes.purchase.supplier'),
            'purchase_order_id' => trans('validation_attributes.purchase.purchase_order'),
            'remarks' => trans('validation_attributes.purchase.remarks'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'warehouse_id' => $this->filled('warehouse_id') ? HashidsHelper::decodeId($this->warehouse_id) : null,
            'supplier_id' => $this->filled('supplier_id') ? HashidsHelper::decodeId($this->supplier_id) : null,
            'purchase_order_id' => $this->filled('purchase_order_id') ? HashidsHelper::decodeId($this->purchase_order_id) : null,
            'remarks' => $this->filled('remarks') ? $this->remarks : null,
        ]);
    }
}
