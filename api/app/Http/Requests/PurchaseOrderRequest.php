<?php

namespace App\Http\Requests;

use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Models\PurchaseOrder;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidSupplier;
use App\Rules\PurchaseOrderStoreValidCode;
use App\Rules\PurchaseOrderUpdateValidCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $purchaseOrder = $this->route('purchase_order');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                return $user->can('viewAny', PurchaseOrder::class) ? true : false;
            case 'read':
                return $user->can('view', PurchaseOrder::class, $purchaseOrder) ? true : false;
            case 'store':
                return $user->can('create', PurchaseOrder::class) ? true : false;
            case 'update':
                return $user->can('update', PurchaseOrder::class, $purchaseOrder) ? true : false;
            case 'delete':
                return $user->can('delete', PurchaseOrder::class, $purchaseOrder) ? true : false;
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
                    'supplier_id' => ['required', 'integer',  new IsValidSupplier($this->company_id)],
                    'code' => ['required', 'string', 'max:255', new PurchaseOrderStoreValidCode($this->company_id)],
                    'date' => ['required', 'date'],
                    'shipping_date' => ['nullable', 'date'],
                    'shipping_address' => ['nullable', 'string', 'max:255'],
                    'remarks' => ['nullable', 'string', 'max:255'],
                    'is_has_invoice' => ['required', 'boolean'],
                    'is_received' => ['required', 'boolean'],
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
                    'code' => ['required', 'string', 'max:255', new PurchaseOrderUpdateValidCode($this->company_id, $this->route('purchase_order'))],
                    'date' => ['required', 'date'],
                    'shipping_date' => ['nullable', 'date'],
                    'shipping_address' => ['nullable', 'string', 'max:255'],
                    'remarks' => ['nullable', 'string', 'max:255'],
                    'is_has_invoice' => ['required', 'boolean'],
                    'is_received' => ['required', 'boolean'],
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
            'company_id' => trans('validation_attributes.purchase_order.company'),
            'branch_id' => trans('validation_attributes.purchase_order.branch'),
            'supplier_id' => trans('validation_attributes.purchase_order.supplier'),
            'code' => trans('validation_attributes.purchase_order.code'),
            'date' => trans('validation_attributes.purchase_order.date'),
            'shipping_date' => trans('validation_attributes.purchase_order.shipping_date'),
            'shipping_address' => trans('validation_attributes.purchase_order.shipping_address'),
            'remarks' => trans('validation_attributes.purchase_order.remarks'),
            'is_has_invoice' => trans('validation_attributes.purchase_order.is_has_invoice'),
            'is_received' => trans('validation_attributes.purchase_order.is_received'),
            'total' => trans('validation_attributes.purchase_order.total'),
            'global_discount_rate' => trans('validation_attributes.purchase_order.global_discount_rate'),
            'global_discount_fixed' => trans('validation_attributes.purchase_order.global_discount_fixed'),
            'grand_total' => trans('validation_attributes.purchase_order.grand_total'),
            'down_payment' => trans('validation_attributes.purchase_order.down_payment'),
            'down_payment_due_days' => trans('validation_attributes.purchase_order.down_payment_due_days'),
            'down_payment_applied' => trans('validation_attributes.purchase_order.down_payment_applied'),
            'down_payment_remaining' => trans('validation_attributes.purchase_order.down_payment_remaining'),
            'is_down_payment_paid_off' => trans('validation_attributes.purchase_order.is_down_payment_paid_off'),
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
                    'remarks' => $this->has('remarks') ? $this['remarks'] : null,
                ]);
                break;
            default:
                $this->merge([]);
                break;
        }
    }
}
