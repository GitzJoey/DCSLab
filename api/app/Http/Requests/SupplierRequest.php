<?php

namespace App\Http\Requests;

use App\Enums\PaymentTermType;
use App\Enums\RecordStatus;
use App\Models\Supplier;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Vinkla\Hashids\Facades\Hashids;

class SupplierRequest extends FormRequest
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
        $supplier = $this->route('supplier');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                return $user->can('viewAny', Supplier::class) ? true : false;
            case 'read':
                return $user->can('view', Supplier::class, $supplier) ? true : false;
            case 'store':
                return $user->can('create', Supplier::class) ? true : false;
            case 'update':
                return $user->can('update', Supplier::class, $supplier) ? true : false;
            case 'delete':
                return $user->can('delete', Supplier::class, $supplier) ? true : false;
            default:
                return false;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $nullableArr = [
            'address' => ['nullable', 'max:255'],
            'contact' => ['nullable', 'max:255'],
            'city' => ['nullable', 'max:255'],
            'tax_id' => ['nullable', 'max:255'],
            'remarks' => ['nullable', 'max:255'],

            'arr_product_id.*' => ['nullable'],
            'arr_main_product_id.*' => ['nullable'],
        ];

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                $rules_read_any = [
                    'company_id' => ['required', new IsValidCompany(), 'bail'],
                    'search' => ['present', 'string'],
                    'paginate' => ['required', 'boolean'],
                    'page' => ['required_if:paginate,true', 'numeric'],
                    'per_page' => ['required_if:paginate,true', 'numeric'],
                    'refresh' => ['nullable', 'boolean'],
                ];

                return $rules_read_any;
            case 'read':
                $rules_read = [

                ];

                return $rules_read;
            case 'store':
                $rules_store = [
                    'company_id' => ['required', new IsValidCompany(), 'bail'],
                    'code' => ['required', 'max:255'],
                    'name' => ['required', 'max:255'],
                    'status' => [new Enum(RecordStatus::class)],
                    'payment_term_type' => [new Enum(PaymentTermType::class)],
                    'payment_term' => ['required', 'numeric'],
                    'taxable_enterprise' => ['required', 'boolean'],

                    'pic_create_user' => ['required', 'boolean'],
                    'pic_contact_person_name' => ['exclude_if:pic_create_user,false', 'max:255'],
                    'pic_email' => ['exclude_if:pic_create_user,false', 'email'],
                    'pic_password' => ['exclude_if:pic_create_user,false', 'max:255'],
                ];

                return array_merge($rules_store, $nullableArr);
            case 'update':
                $rules_update = [
                    'company_id' => ['required', new IsValidCompany(), 'bail'],
                    'code' => ['required', 'max:255'],
                    'name' => ['required', 'max:255'],
                    'status' => [new Enum(RecordStatus::class)],
                    'payment_term_type' => [new Enum(PaymentTermType::class)],
                    'payment_term' => ['required', 'numeric'],
                    'taxable_enterprise' => ['required', 'boolean'],

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
            'company_id' => trans('validation_attributes.supplier.company'),
            'code' => trans('validation_attributes.supplier.code'),
            'name' => trans('validation_attributes.supplier.name'),
            'address' => trans('validation_attributes.supplier.address'),
            'city' => trans('validation_attributes.supplier.city'),
            'contact' => trans('validation_attributes.supplier.contact'),
            'taxable_enterprise' => trans('validation_attributes.supplier.taxable_enterprise'),
            'tax_id' => trans('validation_attributes.supplier.tax_id'),
            'payment_term_type' => trans('validation_attributes.supplier.payment_term_type'),
            'payment_term' => trans('validation_attributes.supplier.payment_term'),
            'status' => trans('validation_attributes.supplier.status'),
            'remarks' => trans('validation_attributes.supplier.remarks'),
            'pic_name' => trans('validation_attributes.supplier.name'),
            'email' => trans('validation_attributes.supplier.email'),
            'arr_product_id.*' => trans('validation_attributes.supplier.product'),
            'arr_main_product_id.*' => trans('validation_attributes.supplier.product'),
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
            case 'readAny':
                $this->merge([
                    'company_id' => $this->has('company_id') ? Hashids::decode($this['company_id'])[0] : '',
                    'search' => $this->has('search') && ! is_null($this->search) ? $this->search : '',
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
                    'taxable_enterprise' => $this->has('taxable_enterprise') ? filter_var($this->taxable_enterprise, FILTER_VALIDATE_BOOLEAN) : false,
                    'payment_term_type' => PaymentTermType::isValid($this->payment_term_type) ? PaymentTermType::resolveToEnum($this->payment_term_type)->value : '',
                    'status' => RecordStatus::isValid($this->status) ? RecordStatus::resolveToEnum($this->status)->value : -1,

                    'pic_create_user' => $this->has('pic_create_user') ? filter_var($this->pic_create_user, FILTER_VALIDATE_BOOLEAN) : false,
                ]);

                $arr_product_id = [];
                if ($this->has('arr_product_id')) {
                    for ($i = 0; $i < count($this->arr_product_id); $i++) {
                        array_push($arr_product_id, Hashids::decode($this['arr_product_id'][$i])[0]);
                    }
                }
                $this->merge(['arr_product_id' => $arr_product_id]);

                $arr_main_product_id = [];
                if ($this->has('arr_main_product_id')) {
                    for ($i = 0; $i < count($this->arr_main_product_id); $i++) {
                        array_push($arr_main_product_id, Hashids::decode($this['arr_main_product_id'][$i])[0]);
                    }
                }
                $this->merge(['arr_main_product_id' => $arr_main_product_id]);

                break;
            default:
                $this->merge([]);
                break;
        }
    }
}
