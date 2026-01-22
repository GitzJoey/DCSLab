<?php

namespace App\Http\Requests;

use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Models\Supplier;
use App\Rules\IsValidCompany;
use App\Rules\SupplierStoreValidCode;
use App\Rules\SupplierUpdateValidCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SupplierRequest extends FormRequest
{
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
                    'user_id' => ['required', 'integer', new IsValidCompany()],
                    'code' => ['required', 'string', 'max:255', new SupplierStoreValidCode($this->company_id)],
                    'name' => ['required', 'string', 'max:255'],
                    'address' => ['nullable', 'string', 'max:255'],
                    'city' => ['nullable', 'string', 'max:255'],
                    'payment_term_type' => ['required', 'string', 'max:255'],
                    'payment_term' => ['required', 'integer'],
                    'taxable_enterprise' => ['required', 'boolean'],
                    'tax_id' => ['required', 'string', 'max:255'],
                    'status' => ['required', 'integer', 'in:'.implode(',', RecordStatusEnum::toArrayValue())],
                    'remarks' => ['nullable', 'string', 'max:255'],
                ];
            case 'update':
                return [
                    'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
                    'code' => ['required', 'string', 'max:255', new SupplierUpdateValidCode($this->company_id, $this->route('supplier'))],
                    'name' => ['required', 'string', 'max:255'],
                    'address' => ['nullable', 'string', 'max:255'],
                    'city' => ['nullable', 'string', 'max:255'],
                    'payment_term_type' => ['required', 'string', 'max:255'],
                    'payment_term' => ['required', 'integer'],
                    'taxable_enterprise' => ['required', 'boolean'],
                    'tax_id' => ['required', 'string', 'max:255'],
                    'status' => ['required', 'integer', 'in:'.implode(',', RecordStatusEnum::toArrayValue())],
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
            'company_id' => trans('validation_attributes.supplier.company'),
            'user_id' => trans('validation_attributes.supplier.user'),
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
                    'address' => $this->has('address') ? $this['address'] : null,
                    'city' => $this->has('city') ? $this['city'] : null,
                    'remarks' => $this->has('remarks') ? $this['remarks'] : null,
                ]);
                break;
            default:
                $this->merge([]);
                break;
        }
    }
}
