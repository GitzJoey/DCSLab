<?php

namespace App\Http\Requests;

use App\Enums\PaymentTermType;
use App\Enums\RoundOn;
use App\Models\CustomerGroup;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Vinkla\Hashids\Facades\Hashids;

class CustomerGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $customergroup = $this->route('customergroup');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'list':
                return $user->can('viewAny', CustomerGroup::class) ? true : false;
            case 'read':
                return $user->can('view', CustomerGroup::class, $customergroup) ? true : false;
            case 'store':
                return $user->can('create', CustomerGroup::class) ? true : false;
            case 'update':
                return $user->can('update', CustomerGroup::class, $customergroup) ? true : false;
            case 'delete':
                return $user->can('delete', CustomerGroup::class, $customergroup) ? true : false;
            default:
                return false;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $nullableArr = [
            'remarks' => 'nullable',
        ];

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'list':
                $rules_list = [
                    'company_id' => ['required', new IsValidCompany(), 'bail'],
                    'search' => ['present', 'string'],
                    'paginate' => ['required', 'boolean'],
                    'page' => ['required_if:paginate,true', 'numeric'],
                    'per_page' => ['required_if:paginate,true', 'numeric'],
                    'refresh' => ['nullable', 'boolean'],
                ];

                return $rules_list;
            case 'read':
                $rules_read = [
                ];

                return $rules_read;
            case 'store':
                $rules_store = [
                    'company_id' => ['required', new IsValidCompany(), 'bail'],
                    'code' => ['required', 'max:255'],
                    'name' => ['required', 'min:2', 'max:255'],
                    'max_open_invoice' => ['required', 'max:100', 'numeric'],
                    'max_outstanding_invoice' => ['required', 'min:0', 'max:100000000', 'numeric'],
                    'max_invoice_age' => ['required', 'min:0', 'max:366', 'numeric'],
                    'payment_term_type' => [new Enum(PaymentTermType::class)],
                    'payment_term' => ['required', 'min:0', 'max:366', 'numeric'],
                    'selling_point' => ['required', 'min:0', 'max:1000', 'numeric'],
                    'selling_point_multiple' => ['required', 'min:0', 'max:100000000', 'numeric'],
                    'sell_at_cost' => ['required', 'boolean'],
                    'price_markup_percent' => ['required', 'min:0', 'max:10', 'numeric'],
                    'price_markup_nominal' => ['required', 'min:0', 'max:100000000', 'numeric'],
                    'price_markdown_percent' => ['required', 'min:0', 'max:1', 'numeric'],
                    'price_markdown_nominal' => ['required', 'min:0', 'max:100000000', 'numeric'],
                    'round_on' => [new Enum(RoundOn::class)],
                    'round_digit' => ['required', 'min:0', 'max:6', 'numeric'],
                ];

                return array_merge($rules_store, $nullableArr);
            case 'update':
                $rules_update = [
                    'company_id' => ['required', new IsValidCompany(), 'bail'],
                    'code' => ['required', 'max:255'],
                    'name' => ['required', 'min:2', 'max:255'],
                    'max_open_invoice' => ['required', 'max:100', 'numeric'],
                    'max_outstanding_invoice' => ['required', 'min:0', 'max:4294967295', 'numeric'],
                    'max_invoice_age' => ['required', 'min:0', 'max:366', 'numeric'],
                    'payment_term_type' => [new Enum(PaymentTermType::class)],
                    'payment_term' => ['required', 'min:0', 'max:366', 'numeric'],
                    'selling_point' => ['required', 'min:0', 'max:1000', 'numeric'],
                    'selling_point_multiple' => ['required', 'min:0', 'max:100000000', 'numeric'],
                    'sell_at_cost' => ['required', 'boolean'],
                    'price_markup_percent' => ['required', 'min:0', 'max:10', 'numeric'],
                    'price_markup_nominal' => ['required', 'min:0', 'max:100000000', 'numeric'],
                    'price_markdown_percent' => ['required', 'min:0', 'max:1', 'numeric'],
                    'price_markdown_nominal' => ['required', 'min:0', 'max:100000000', 'numeric'],
                    'round_on' => [new Enum(RoundOn::class)],
                    'round_digit' => ['required', 'min:0', 'max:6', 'numeric'],
                ];

                return array_merge($rules_update, $nullableArr);
            default:
                return [
                    '' => 'required',
                ];
        }
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
            'round_on' => trans('validation_attributes.customer_group.round_on'),
            'round_digit' => trans('validation_attributes.customer_group.round_digit'),
            'remarks' => trans('validation_attributes.customer_group.remarks'),
        ];
    }

    public function validationData()
    {
        $additionalArray = [];

        return array_merge($this->all(), $additionalArray);
    }

    public function prepareForValidation()
    {
        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'list':
                $this->merge([
                    'company_id' => $this->has('company_id') ? Hashids::decode($this['company_id'])[0] : '',
                    'paginate' => $this->has('paginate') ? filter_var($this->paginate, FILTER_VALIDATE_BOOLEAN) : true,
                ]);
                break;
            case 'read':
                $this->merge([]);
                break;
            case 'store':
            case 'update':
                $this->merge([
                    'company_id' => $this->has('company_id') ? Hashids::decode($this['company_id'])[0] : '',
                    'payment_term_type' => PaymentTermType::isValid($this->payment_term_type) ? PaymentTermType::resolveToEnum($this->payment_term_type)->value : '',
                    'round_on' => RoundOn::isValid($this->round_on) ? RoundOn::resolveToEnum($this->round_on)->value : -1,
                    'sell_at_cost' => $this->has('sell_at_cost') ? filter_var($this->sell_at_cost, FILTER_VALIDATE_BOOLEAN) : false,
                ]);
                break;
            default:
                $this->merge([]);
                break;
        }
    }
}
