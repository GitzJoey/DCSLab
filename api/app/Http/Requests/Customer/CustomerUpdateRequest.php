<?php

namespace App\Http\Requests\Customer;

use App\Enums\PaymentTermTypeEnum;
use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Models\Customer;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class CustomerUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $customer = $this->route('customer');

        return $user->can('update', Customer::class, $customer) ? true : false;
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'code' => ['required', 'string', 'max:255'],
            'is_member' => ['required', 'boolean'],
            'name' => ['required', 'string', 'max:255'],
            'group_id' => ['nullable', 'integer', new ExistsForCompany('customer_groups', $this->company_id)],
            'zone' => ['nullable', 'string', 'max:255'],
            'max_open_invoice' => ['required', 'integer', 'min:0'],
            'max_outstanding_invoice' => ['required', 'numeric', 'min:0'],
            'max_invoice_age' => ['required', 'integer', 'min:0'],
            'payment_term_type' => ['nullable', new Enum(PaymentTermTypeEnum::class)],
            'payment_term' => ['required', 'integer', 'min:0'],
            'taxable_enterprise' => ['required', 'boolean'],
            'tax_id' => ['nullable', 'string', 'max:255'],
            'status' => ['required', new Enum(RecordStatusEnum::class)],
            'remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.customer.company'),
            'code' => trans('validation_attributes.customer.code'),
            'is_member' => trans('validation_attributes.customer.is_member'),
            'name' => trans('validation_attributes.customer.name'),
            'zone' => trans('validation_attributes.customer.zone'),
            'max_open_invoice' => trans('validation_attributes.customer.max_open_invoice'),
            'max_outstanding_invoice' => trans('validation_attributes.customer.max_outstanding_invoice'),
            'max_invoice_age' => trans('validation_attributes.customer.max_invoice_age'),
            'payment_term_type' => trans('validation_attributes.customer.payment_term_type'),
            'payment_term' => trans('validation_attributes.customer.payment_term'),
            'taxable_enterprise' => trans('validation_attributes.customer.taxable_enterprise'),
            'tax_id' => trans('validation_attributes.customer.tax_id'),
            'status' => trans('validation_attributes.customer.status'),
            'remarks' => trans('validation_attributes.customer.remarks'),
        ];
    }

    public function validationData()
    {
        return $this->all();
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'group_id' => $this->filled('group_id') ? HashidsHelper::decodeId($this->group_id) : null,
            'payment_term_type' => PaymentTermTypeEnum::isValid($this->payment_term_type) ? PaymentTermTypeEnum::resolveToEnum($this->payment_term_type)->value : null,
            'status' => RecordStatusEnum::isValid($this->status) ? RecordStatusEnum::resolveToEnum($this->status)->value : null,
            'remarks' => $this->filled('remarks') ? $this['remarks'] : null,
        ]);
    }
}
