<?php

namespace App\Http\Requests;

use App\Enums\PaymentTermType;
use App\Enums\RecordStatus;
use App\Models\Customer;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Vinkla\Hashids\Facades\Hashids;

class CustomerRequest extends FormRequest
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
        $customer = $this->route('customer');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'list':
                return $user->can('viewAny', Customer::class) ? true : false;
            case 'read':
                return $user->can('view', Customer::class, $customer) ? true : false;
            case 'store':
                return $user->can('create', Customer::class) ? true : false;
            case 'update':
                return $user->can('update', Customer::class, $customer) ? true : false;
            case 'delete':
                return $user->can('delete', Customer::class, $customer) ? true : false;
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
            'zone' => ['nullable', 'max:255'],
            'tax_id' => ['nullable', 'max:255'],
            'remarks' => ['nullable', 'max:255'],
            'arr_customer_address_id.*' => ['nullable'],
            'arr_customer_address_city.*' => ['nullable', 'max:255'],
            'arr_customer_address_contact.*' => ['nullable', 'max:255'],
            'arr_customer_address_remarks.*' => ['nullable', 'max:255'],
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
                    'customer_group_id' => ['required'],
                    'code' => ['required', 'max:255'],
                    'is_member' => ['required', 'boolean'],
                    'name' => ['required', 'min:3', 'max:255'],
                    'max_open_invoice' => ['required', 'max:100', 'numeric'],
                    'max_outstanding_invoice' => ['required', 'min:0', 'max:100000000', 'numeric'],
                    'max_invoice_age' => ['required', 'min:0', 'max:366', 'numeric'],
                    'payment_term_type' => [new Enum(PaymentTermType::class)],
                    'payment_term' => ['required', 'min:0', 'max:366', 'numeric'],
                    'taxable_enterprise' => ['required', 'boolean'],
                    'status' => [new Enum(RecordStatus::class)],

                    'arr_customer_address_address.*' => ['required', 'max:255'],
                    'arr_customer_address_is_main.*' => ['required', 'boolean'],

                    'pic_create_user' => ['required', 'boolean'],
                    'pic_contact_person_name' => ['exclude_if:pic_create_user,false', 'max:255'],
                    'pic_email' => ['exclude_if:pic_create_user,false', 'email'],
                    'pic_password' => ['exclude_if:pic_create_user,false', 'max:255'],
                ];

                return array_merge($rules_store, $nullableArr);
            case 'update':
                $rules_update = [
                    'company_id' => ['required', new IsValidCompany(), 'bail'],
                    'customer_group_id' => ['required'],
                    'code' => ['required', 'max:255'],
                    'is_member' => ['required', 'boolean'],
                    'name' => ['required', 'min:3', 'max:255'],
                    'max_open_invoice' => ['required', 'max:100', 'numeric'],
                    'max_outstanding_invoice' => ['required', 'min:0', 'max:100000000', 'numeric'],
                    'max_invoice_age' => ['required', 'min:0', 'max:366', 'numeric'],
                    'payment_term_type' => [new Enum(PaymentTermType::class)],
                    'payment_term' => ['required', 'min:0', 'max:366', 'numeric'],
                    'taxable_enterprise' => ['required', 'boolean'],
                    'status' => [new Enum(RecordStatus::class)],

                    'arr_customer_address_address.*' => ['required', 'max:255'],
                    'arr_customer_address_is_main.*' => ['required', 'boolean'],

                    'pic_create_user' => ['required', 'boolean'],
                    'pic_contact_person_name' => ['exclude_if:pic_create_user,false', 'max:255'],
                    'pic_email' => ['exclude_if:pic_create_user,false', 'email'],
                    'pic_password' => ['exclude_if:pic_create_user,false', 'max:255'],
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
            'company_id' => trans('validation_attributes.customer.company'),
            'code' => trans('validation_attributes.customer.code'),
            'customer_group_id' => trans('validation_attributes.customer.customer_group'),
            'name' => trans('validation_attributes.customer.name'),
            'is_member' => trans('validation_attributes.customer.is_member'),
            'zone' => trans('validation_attributes.customer.zone'),
            'max_open_invoice' => trans('validation_attributes.customer.max_open_invoice'),
            'max_outstanding_invoice' => trans('validation_attributes.customer.max_outstanding_invoice'),
            'max_invoice_age' => trans('validation_attributes.customer.max_invoice_age'),
            'payment_term_type' => trans('validation_attributes.customer.payment_term_type'),
            'payment_term' => trans('validation_attributes.customer.payment_term'),
            'taxable_enterprise' => trans('validation_attributes.customer.taxable_enterprise'),
            'tax_id' => trans('validation_attributes.customer.tax_id'),
            'remarks' => trans('validation_attributes.customer.remarks'),
            'status' => trans('validation_attributes.customer.status'),
            'arr_customer_address_id' => trans('validation_attributes.customer.customer_address.id'),
            'arr_customer_address_address' => trans('validation_attributes.customer.customer_address.address'),
            'arr_customer_address_city' => trans('validation_attributes.customer.customer_address.city'),
            'arr_customer_address_contact' => trans('validation_attributes.customer.customer_address.contact'),
            'arr_customer_address_is_main' => trans('validation_attributes.customer.customer_address.is_main'),
            'arr_customer_address_remarks' => trans('validation_attributes.customer.customer_address.remarks'),
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
                    'customer_group_id' => $this->has('customer_group_id') ? Hashids::decode($this['customer_group_id'])[0] : '',
                    'is_member' => $this->has('is_member') ? filter_var($this->is_member, FILTER_VALIDATE_BOOLEAN) : false,
                    'payment_term_type' => PaymentTermType::isValid($this->payment_term_type) ? PaymentTermType::resolveToEnum($this->payment_term_type)->value : '',
                    'taxable_enterprise' => $this->has('taxable_enterprise') ? filter_var($this->taxable_enterprise, FILTER_VALIDATE_BOOLEAN) : false,
                    'status' => RecordStatus::isValid($this->status) ? RecordStatus::resolveToEnum($this->status)->value : -1,

                    'pic_create_user' => $this->has('pic_create_user') ? filter_var($this->pic_create_user, FILTER_VALIDATE_BOOLEAN) : false,
                ]);

                $arr_customer_address_id = [];
                if ($this->has('arr_customer_address_id')) {
                    for ($i = 0; $i < count($this->arr_customer_address_id); $i++) {
                        if ($this->arr_customer_address_id[$i] != '') {
                            array_push($arr_customer_address_id, Hashids::decode($this->arr_customer_address_id[$i])[0]);
                        } else {
                            array_push($arr_customer_address_id, null);
                        }
                    }
                }
                $this->merge(['arr_customer_address_id' => $arr_customer_address_id]);

                $arr_customer_address = [];
                if ($this->has('arr_customer_address')) {
                    for ($i = 0; $i < count($this->arr_customer_address); $i++) {
                        if ($this->arr_customer_address[$i] != '') {
                            array_push($arr_customer_address, $this->arr_customer_address[$i]);
                        } else {
                            array_push($arr_customer_address, '');
                        }
                    }
                }
                $this->merge(['arr_customer_address' => $arr_customer_address]);

                $arr_customer_city = [];
                if ($this->has('arr_customer_city')) {
                    for ($i = 0; $i < count($this->arr_customer_city); $i++) {
                        if ($this->arr_customer_city[$i] != '') {
                            array_push($arr_customer_city, $this->arr_customer_city[$i]);
                        } else {
                            array_push($arr_customer_city, '');
                        }
                    }
                }
                $this->merge(['arr_customer_city' => $arr_customer_city]);

                $arr_customer_contact = [];
                if ($this->has('arr_customer_contact')) {
                    for ($i = 0; $i < count($this->arr_customer_contact); $i++) {
                        if ($this->arr_customer_contact[$i] != '') {
                            array_push($arr_customer_contact, $this->arr_customer_contact[$i]);
                        } else {
                            array_push($arr_customer_contact, null);
                        }
                    }
                }
                $this->merge(['arr_customer_contact' => $arr_customer_contact]);

                $arr_customer_address_is_main = [];
                if ($this->has('arr_customer_address_is_main')) {
                    for ($i = 0; $i < count($this->arr_customer_address_is_main); $i++) {
                        $is_main = filter_var($this->arr_customer_address_is_main[$i], FILTER_VALIDATE_BOOLEAN) ? true : false;
                        array_push($arr_customer_address_is_main, $is_main);
                    }
                }
                $this->merge(['arr_customer_address_is_main' => $arr_customer_address_is_main]);

                $arr_customer_remarks = [];
                if ($this->has('arr_customer_remarks')) {
                    for ($i = 0; $i < count($this->arr_customer_remarks); $i++) {
                        if ($this->arr_customer_remarks[$i] != '') {
                            array_push($arr_customer_remarks, $this->arr_customer_remarks[$i]);
                        } else {
                            array_push($arr_customer_remarks, '');
                        }
                    }
                }
                $this->merge(['arr_customer_remarks' => $arr_customer_remarks]);

                break;
            default:
                $this->merge([]);
                break;
        }
    }
}
