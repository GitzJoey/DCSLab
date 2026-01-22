<?php

namespace App\Http\Requests;

use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Models\PurchaseReceipt;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidPurchase;
use App\Rules\IsValidWarehouse;
use App\Rules\PurchaseReceiptStoreValidCode;
use App\Rules\PurchaseReceiptUpdateValidCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseReceiptRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $purchaseReceipt = $this->route('purchase_receipt');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                return $user->can('viewAny', PurchaseReceipt::class) ? true : false;
            case 'read':
                return $user->can('view', PurchaseReceipt::class, $purchaseReceipt) ? true : false;
            case 'store':
                return $user->can('create', PurchaseReceipt::class) ? true : false;
            case 'update':
                return $user->can('update', PurchaseReceipt::class, $purchaseReceipt) ? true : false;
            case 'delete':
                return $user->can('delete', PurchaseReceipt::class, $purchaseReceipt) ? true : false;
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
                    'code' => ['required', 'string', 'max:255', new PurchaseReceiptStoreValidCode($this->company_id)],
                    'purchase_id' => ['required', 'integer', new IsValidPurchase($this->company_id)],
                    'warehouse_id' => ['required', 'integer', new IsValidWarehouse($this->company_id, true)],
                    'is_posted' => ['required', 'boolean'],
                    'is_valid' => ['required', 'boolean'],
                ];
            case 'update':
                return [
                    'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
                    'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
                    'code' => ['required', 'string', 'max:255', new PurchaseReceiptUpdateValidCode($this->company_id, $this->route('purchase_receipt'))],
                    'purchase_id' => ['required', 'integer', new IsValidPurchase($this->company_id)],
                    'warehouse_id' => ['required', 'integer', new IsValidWarehouse($this->company_id, true)],
                    'is_posted' => ['required', 'boolean'],
                    'is_valid' => ['required', 'boolean'],
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
            'company_id' => trans('validation_attributes.purchase_receipt.company'),
            'branch_id' => trans('validation_attributes.purchase_receipt.branch'),
            'code' => trans('validation_attributes.purchase_receipt.code'),
            'purchase_id' => trans('validation_attributes.purchase_receipt.purchase'),
            'warehouse_id' => trans('validation_attributes.purchase_receipt.warehouse'),
            'is_posted' => trans('validation_attributes.purchase_receipt.is_posted'),
            'is_valid' => trans('validation_attributes.purchase_receipt.is_valid'),
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
