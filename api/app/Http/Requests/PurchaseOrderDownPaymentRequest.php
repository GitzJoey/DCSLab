<?php

namespace App\Http\Requests;

use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Models\PurchaseOrderDownPayment;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidCompany;
use App\Rules\IsValidPurchaseOrder;
use App\Rules\PurchaseOrderDownPaymentStoreValidCode;
use App\Rules\PurchaseOrderDownPaymentUpdateValidCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderDownPaymentRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $purchaseOrderDownPayment = $this->route('purchase_order_down_payments');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                return $user->can('viewAny', PurchaseOrderDownPayment::class) ? true : false;
            case 'read':
                return $user->can('view', PurchaseOrderDownPayment::class, $purchaseOrderDownPayment) ? true : false;
            case 'store':
                return $user->can('create', PurchaseOrderDownPayment::class) ? true : false;
            case 'update':
                return $user->can('update', PurchaseOrderDownPayment::class, $purchaseOrderDownPayment) ? true : false;
            case 'delete':
                return $user->can('delete', PurchaseOrderDownPayment::class, $purchaseOrderDownPayment) ? true : false;
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
                    'purchase_order_id' => ['required', 'integer', 'bail', new IsValidPurchaseOrder()],
                    'code' => ['required', 'string', 'max:255', new PurchaseOrderDownPaymentStoreValidCode($this->company_id)],
                    'cash_account_id' => ['required', 'integer', 'bail', new IsValidCashAccount($this->company_id)],
                    'amount' => ['nullable', 'numeric', 'min:0'],
                    'remarks' => ['nullable', 'string', 'max:255'],
                ];
            case 'update':
                return [
                    'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
                    'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
                    'purchase_order_id' => ['required', 'integer', 'bail', new IsValidPurchaseOrder()],
                    'code' => ['required', 'string', 'max:255', new PurchaseOrderDownPaymentUpdateValidCode($this->company_id, $this->route('purchase_order_down_payments'))],
                    'cash_account_id' => ['required', 'integer', 'bail', new IsValidCashAccount($this->company_id)],
                    'amount' => ['nullable', 'numeric', 'min:0'],
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
            'company_id' => trans('validation_attributes.purchase_order_down_payments.company'),
            'branch_id' => trans('validation_attributes.purchase_order_down_payments.branch'),
            'purchase_order_id' => trans('validation_attributes.purchase_order_down_payments.purchase_order'),
            'code' => trans('validation_attributes.purchase_order_down_payments.code'),
            'date' => trans('validation_attributes.purchase_order_down_payments.date'),
            'cash_account_id' => trans('validation_attributes.purchase_order_down_payments.cash_account'),
            'amount' => trans('validation_attributes.purchase_order_down_payments.amount'),
            'remarks' => trans('validation_attributes.purchase_order_down_payments.remarks'),
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
