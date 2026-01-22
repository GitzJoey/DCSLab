<?php

namespace App\Http\Requests;

use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Models\SalesOrder;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidCustomer;
use App\Rules\IsValidCustomerAddress;
use App\Rules\SalesOrderStoreValidCode;
use App\Rules\SalesOrderUpdateValidCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SalesOrderRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $salesOrder = $this->route('sales_order');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                return $user->can('viewAny', SalesOrder::class) ? true : false;
            case 'read':
                return $user->can('view', SalesOrder::class, $salesOrder) ? true : false;
            case 'store':
                return $user->can('create', SalesOrder::class) ? true : false;
            case 'update':
                return $user->can('update', SalesOrder::class, $salesOrder) ? true : false;
            case 'delete':
                return $user->can('delete', SalesOrder::class, $salesOrder) ? true : false;
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
                    'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
                    'code' => ['required', 'string', 'max:255', new SalesOrderStoreValidCode($this->company_id)],
                    'date' => ['required', 'date'],
                    'customer_id' => ['required', 'integer', new IsValidCustomer($this->company_id)],
                    'customer_address_id' => ['nullable', 'integer', new IsValidCustomerAddress($this->company_id)],
                    'shipping_date' => ['nullable', 'date'],
                    'remarks' => ['nullable', 'string', 'max:255'],
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
            case 'update':
                return [
                    'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
                    'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
                    'code' => ['required', 'string', 'max:255', new SalesOrderUpdateValidCode($this->company_id, $this->route('sales_order'))],
                    'date' => ['required', 'date'],
                    'customer_id' => ['required', 'integer', new IsValidCustomer($this->company_id)],
                    'customer_address_id' => ['nullable', 'integer', new IsValidCustomerAddress($this->company_id)],
                    'shipping_date' => ['nullable', 'date'],
                    'remarks' => ['nullable', 'string', 'max:255'],
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
                    'customer_address_id' => $this->has('customer_address_id') ? $this['customer_address_id'] : null,
                    'shipping_date' => $this->has('shipping_date') ? $this['shipping_date'] : null,
                    'remarks' => $this->has('remarks') ? $this['remarks'] : null,
                ]);
                break;
            default:
                $this->merge([]);
                break;
        }
    }
}
