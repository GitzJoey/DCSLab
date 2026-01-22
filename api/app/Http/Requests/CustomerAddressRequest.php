<?php

namespace App\Http\Requests;

use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Models\CustomerAddress;
use App\Rules\IsValidCompany;
use App\Rules\IsValidCustomer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CustomerAddressRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $customerAddress = $this->route('customer_address');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                return $user->can('viewAny', CustomerAddress::class) ? true : false;
            case 'read':
                return $user->can('view', CustomerAddress::class, $customerAddress) ? true : false;
            case 'store':
                return $user->can('create', CustomerAddress::class) ? true : false;
            case 'update':
                return $user->can('update', CustomerAddress::class, $customerAddress) ? true : false;
            case 'delete':
                return $user->can('delete', CustomerAddress::class, $customerAddress) ? true : false;
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
        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                return [
                    'refresh' => ['required', 'boolean'],
                    'with_trashed' => ['required', 'boolean'],

                    'search' => ['nullable', 'string'],
                    'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
                    'status' => ['nullable', 'integer', 'in:'.implode(',', RecordStatusEnum::toArrayValue())],

                    'paginate' => ['required', 'boolean'],
                    'page' => ['nullable', 'required_if:paginate,true', 'numeric', 'min:1'],
                    'per_page' => ['nullable', 'required_if:paginate,true', 'numeric', 'min:10'],
                    'limit' => ['nullable', 'integer', 'min:1'],
                ];
            case 'read':
                return [];
            case 'store':
                return [
                    'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
                    'customer_id' => ['required', 'integer', new IsValidCustomer($this->company_id)],
                    'address' => ['required', 'string', 'max:255'],
                    'city' => ['nullable', 'string', 'max:255'],
                    'contact' => ['nullable', 'string', 'max:255'],
                    'is_main' => ['required', 'boolean'],
                    'remarks' => ['nullable', 'string', 'max:255'],
                ];
            case 'update':
                return [
                    'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
                    'customer_id' => ['required', 'integer', new IsValidCustomer($this->company_id)],
                    'address' => ['required', 'string', 'max:255'],
                    'city' => ['nullable', 'string', 'max:255'],
                    'contact' => ['nullable', 'string', 'max:255'],
                    'is_main' => ['required', 'boolean'],
                    'remarks' => ['nullable', 'string', 'max:255'],
                ];
            case 'delete':
                return [

                ];
            default:
                return [
                    '' => 'required',
                ];
        }
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
                    'with_trashed' => $this->has('with_trashed') ? $this->with_trashed : null,

                    'search' => $this->has('search') ? $this->search : null,
                    'company_id' => $this->has('company_id') ? HashidsHelper::decodeId($this->company_id) : null,

                    'page' => $this->has('page') ? $this->page : null,
                    'per_page' => $this->has('per_page') ? $this->per_page : null,
                    'limit' => $this->has('limit') ? $this->limit : null,
                ]);
                break;
            case 'read':
                $this->merge([]);
                break;
            case 'store':
            case 'update':
                $this->merge([
                    'company_id' => $this->has('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
                    'city' => $this->has('city') ? $this['city'] : null,
                    'contact' => $this->has('contact') ? $this['contact'] : null,
                    'remarks' => $this->has('remarks') ? $this['remarks'] : null,
                ]);
                break;
            default:
                $this->merge([]);
                break;
        }
    }
}
