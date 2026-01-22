<?php

namespace App\Http\Requests\CustomerGroup;

use App\Enums\PaymentTermTypeEnum;
use App\Enums\RoundingTypeEnum;
use App\Helpers\HashidsHelper;
use App\Models\CustomerGroup;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class CustomerGroupStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', CustomerGroup::class);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'code' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'max_open_invoice' => ['required', 'integer', 'min:0'],
            'max_outstanding_invoice' => ['required', 'numeric', 'min:0'],
            'max_invoice_age' => ['required', 'integer', 'min:0'],
            'payment_term_type' => [new Enum(PaymentTermTypeEnum::class)],
            'payment_term' => ['required', 'integer', 'min:0'],
            'selling_point' => ['required', 'integer', 'max:255'],
            'selling_point_multiple' => ['required', 'numeric', 'min:0'],
            'sell_at_cost' => ['required', 'boolean'],
            'price_markup_percent' => ['required', 'numeric', 'min:0'],
            'price_markup_nominal' => ['required', 'numeric', 'min:0'],
            'price_markdown_percent' => ['required', 'numeric', 'min:0'],
            'price_markdown_nominal' => ['required', 'numeric', 'min:0'],
            'rounding_type' => [new Enum(RoundingTypeEnum::class)],
            'rounding_digit' => ['required', 'integer', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.customer_group.company'),
            'code' => trans('validation_attributes.customer_group.code'),
            'name' => trans('validation_attributes.customer_group.name'),
            'max_open_invoice' => trans('validation_attributes.customer_group.max_open_invoice'),
            'max_outstanding_invoice' => trans('validation_attributes.customer_group.max_outstanding_invoice'),
            'max_invoice_age' => trans('validation_attributes.customer_group.max_invoice_age'),
            'payment_term_type' => trans('validation_attributes.customer_group.payment_term_type'),
            'payment_term' => trans('validation_attributes.customer_group.payment_term'),
            'selling_point' => trans('validation_attributes.customer_group.selling_point'),
            'selling_point_multiple' => trans('validation_attributes.customer_group.selling_point_multiple'),
            'sell_at_cost' => trans('validation_attributes.customer_group.sell_at_cost'),
            'price_markup_percent' => trans('validation_attributes.customer_group.price_markup_percent'),
            'price_markup_nominal' => trans('validation_attributes.customer_group.price_markup_nominal'),
            'price_markdown_percent' => trans('validation_attributes.customer_group.price_markdown_percent'),
            'price_markdown_nominal' => trans('validation_attributes.customer_group.price_markdown_nominal'),
            'rounding_type' => trans('validation_attributes.customer_group.rounding_type'),
            'rounding_digit' => trans('validation_attributes.customer_group.rounding_digit'),
            'remarks' => trans('validation_attributes.customer_group.remarks'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'payment_term_type' => PaymentTermTypeEnum::isValid($this->payment_term_type) ? PaymentTermTypeEnum::resolveToEnum($this->payment_term_type)->value : null,
            'rounding_type' => RoundingTypeEnum::isValid($this->rounding_type) ? RoundingTypeEnum::resolveToEnum($this->rounding_type)->value : null,
            'remarks' => $this->has('remarks') ? $this['remarks'] : null,
        ]);
    }
}
