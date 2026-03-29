<?php

namespace App\Http\Requests\SalesOrder;

use App\Helpers\HashidsHelper;
use App\Models\SalesOrder;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidCustomer;
use App\Rules\IsValidCustomerAddress;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SalesOrderStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', SalesOrder::class);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'code' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'customer_id' => ['required', 'integer', new IsValidCustomer($this->company_id)],
            'customer_address_id' => ['present', 'nullable', 'integer', new IsValidCustomerAddress($this->company_id)],
            'shipping_date' => ['present', 'nullable', 'date'],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],
            'is_has_invoice' => ['required', 'boolean'],
            'is_sent' => ['required', 'boolean'],
            'total' => ['required', 'numeric', 'min:0'],
            'global_discount_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'global_discount_fixed' => ['required', 'numeric', 'min:0'],
            'grand_total' => ['required', 'numeric', 'min:0'],
            'down_payment' => ['required', 'numeric', 'min:0'],
            'down_payment_due_days' => ['required', 'integer', 'min:0'],
            'down_payment_applied' => ['required', 'numeric', 'min:0'],
            'down_payment_remaining' => ['required', 'numeric', 'min:0'],
            'is_down_payment_paid_off' => ['required', 'boolean'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.sales_order.company'),
            'branch_id' => trans('validation_attributes.sales_order.branch'),
            'code' => trans('validation_attributes.sales_order.code'),
            'date' => trans('validation_attributes.sales_order.date'),
            'customer_id' => trans('validation_attributes.sales_order.customer'),
            'customer_address_id' => trans('validation_attributes.sales_order.customer_address'),
            'shipping_date' => trans('validation_attributes.sales_order.shipping_date'),
            'remarks' => trans('validation_attributes.sales_order.remarks'),
            'is_has_invoice' => trans('validation_attributes.sales_order.is_has_invoice'),
            'is_sent' => trans('validation_attributes.sales_order.is_sent'),
            'total' => trans('validation_attributes.sales_order.total'),
            'global_discount_rate' => trans('validation_attributes.sales_order.global_discount_rate'),
            'global_discount_fixed' => trans('validation_attributes.sales_order.global_discount_fixed'),
            'grand_total' => trans('validation_attributes.sales_order.grand_total'),
            'down_payment' => trans('validation_attributes.sales_order.down_payment'),
            'down_payment_due_days' => trans('validation_attributes.sales_order.down_payment_due_days'),
            'down_payment_applied' => trans('validation_attributes.sales_order.down_payment_applied'),
            'down_payment_remaining' => trans('validation_attributes.sales_order.down_payment_remaining'),
            'is_down_payment_paid_off' => trans('validation_attributes.sales_order.is_down_payment_paid_off'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'customer_id' => $this->filled('customer_id') ? HashidsHelper::decodeId($this->customer_id) : null,
            'customer_address_id' => $this->filled('customer_address_id') ? HashidsHelper::decodeId($this->customer_address_id) : null,
            'shipping_date' => $this->filled('shipping_date') ? $this['shipping_date'] : null,
            'remarks' => $this->filled('remarks') ? $this['remarks'] : null,
        ]);
    }
}
