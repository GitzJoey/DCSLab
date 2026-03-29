<?php

namespace App\Http\Requests\CustomerAddress;

use App\Helpers\HashidsHelper;
use App\Rules\IsValidCompany;
use App\Rules\IsValidCustomer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CustomerAddressUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $customerAddress = $this->route('customer_address');

        return $user->can('update', $customerAddress);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'customer_id' => ['required', 'integer', new IsValidCustomer($this->company_id)],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['present', 'nullable', 'string', 'max:255'],
            'contact' => ['present', 'nullable', 'string', 'max:255'],
            'is_main' => ['required', 'boolean'],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.customer_address.company'),
            'customer_id' => trans('validation_attributes.customer_address.customer'),
            'address' => trans('validation_attributes.customer_address.address'),
            'city' => trans('validation_attributes.customer_address.city'),
            'contact' => trans('validation_attributes.customer_address.contact'),
            'is_main' => trans('validation_attributes.customer_address.is_main'),
            'remarks' => trans('validation_attributes.customer_address.remarks'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'customer_id' => $this->filled('customer_id') ? HashidsHelper::decodeId($this->customer_id) : null,
            'city' => $this->filled('city') ? $this['city'] : null,
            'contact' => $this->filled('contact') ? $this['contact'] : null,
            'remarks' => $this->filled('remarks') ? $this['remarks'] : null,
        ]);
    }
}
