<?php

namespace App\Http\Requests\Supplier;

use App\Enums\PaymentTermTypeEnum;
use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Models\Supplier;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class SupplierStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', Supplier::class);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'code' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['present', 'nullable', 'string', 'max:255'],
            'city' => ['present', 'nullable', 'string', 'max:255'],
            'payment_term_type' => ['required', new Enum(PaymentTermTypeEnum::class)],
            'payment_term' => ['required', 'integer'],
            'taxable_enterprise' => ['required', 'boolean'],
            'tax_id' => ['present', 'nullable', 'string', 'max:255'],
            'status' => ['required', new Enum(RecordStatusEnum::class)],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.supplier.company'),
            'code' => trans('validation_attributes.supplier.code'),
            'name' => trans('validation_attributes.supplier.name'),
            'address' => trans('validation_attributes.supplier.address'),
            'city' => trans('validation_attributes.supplier.city'),
            'payment_term_type' => trans('validation_attributes.supplier.payment_term_type'),
            'payment_term' => trans('validation_attributes.supplier.payment_term'),
            'taxable_enterprise' => trans('validation_attributes.supplier.taxable_enterprise'),
            'tax_id' => trans('validation_attributes.supplier.tax_id'),
            'status' => trans('validation_attributes.supplier.status'),
            'remarks' => trans('validation_attributes.supplier.remarks'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'payment_term_type' => PaymentTermTypeEnum::isValid($this->payment_term_type) ? PaymentTermTypeEnum::resolveToEnum($this->payment_term_type)->value : null,
            'status' => RecordStatusEnum::isValid($this->status) ? RecordStatusEnum::resolveToEnum($this->status)->value : null,
        ]);
    }
}
