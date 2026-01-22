<?php

namespace App\Http\Requests;

use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Models\PurchaseReceiptProductUnit;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidProduct;
use App\Rules\IsValidProductUnit;
use App\Rules\IsValidPurchase;
use App\Rules\IsValidPurchaseReceipt;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseReceiptProductUnitRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $purchaseReceiptProductUnit = $this->route('purchase_receipt_product_unit');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                return $user->can('viewAny', PurchaseReceiptProductUnit::class) ? true : false;
            case 'read':
                return $user->can('view', PurchaseReceiptProductUnit::class, $purchaseReceiptProductUnit) ? true : false;
            case 'store':
                return $user->can('create', PurchaseReceiptProductUnit::class) ? true : false;
            case 'update':
                return $user->can('update', PurchaseReceiptProductUnit::class, $purchaseReceiptProductUnit) ? true : false;
            case 'delete':
                return $user->can('delete', PurchaseReceiptProductUnit::class, $purchaseReceiptProductUnit) ? true : false;
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
                    'purchase_receipt_id' => ['required', 'integer', new IsValidPurchaseReceipt($this->company_id)],
                    'purchase_id' => ['required', 'integer', new IsValidPurchase($this->company_id)],
                    'qty' => ['required', 'numeric', 'min:0'],
                    'product_id' => ['required', 'integer', new IsValidProduct($this->company_id)],
                    'product_unit_id' => ['required', 'integer', new IsValidProductUnit($this->company_id)],
                    'product_unit_amount_per_unit' => ['required', 'numeric', 'min:0'],
                    'product_unit_amount_total' => ['required', 'numeric', 'min:0'],
                    'is_has_purchase' => ['required', 'boolean'],
                ];
            case 'update':
                return [
                    'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
                    'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
                    'purchase_receipt_id' => ['required', 'integer', new IsValidPurchaseReceipt($this->company_id)],
                    'purchase_id' => ['required', 'integer', new IsValidPurchase($this->company_id)],
                    'qty' => ['required', 'numeric', 'min:0'],
                    'product_id' => ['required', 'integer', new IsValidProduct($this->company_id)],
                    'product_unit_id' => ['required', 'integer', new IsValidProductUnit($this->company_id)],
                    'product_unit_amount_per_unit' => ['required', 'numeric', 'min:0'],
                    'product_unit_amount_total' => ['required', 'numeric', 'min:0'],
                    'is_has_purchase' => ['required', 'boolean'],
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
            'company_id' => trans('validation_attributes.purchase_receipt_product_unit.company'),
            'branch_id' => trans('validation_attributes.purchase_receipt_product_unit.branch'),
            'purchase_receipt_id' => trans('validation_attributes.purchase_receipt_product_unit.purchase_receipt'),
            'purchase_id' => trans('validation_attributes.purchase_receipt_product_unit.purchase'),
            'qty' => trans('validation_attributes.purchase_receipt_product_unit.qty'),
            'product_id' => trans('validation_attributes.purchase_receipt_product_unit.product'),
            'product_unit_id' => trans('validation_attributes.purchase_receipt_product_unit.product_unit'),
            'product_unit_amount_per_unit' => trans('validation_attributes.purchase_receipt_product_unit.product_unit_amount_per_unit'),
            'product_unit_amount_total' => trans('validation_attributes.purchase_receipt_product_unit.product_unit_amount_total'),
            'is_has_purchase' => trans('validation_attributes.purchase_receipt_product_unit.is_has_purchase'),
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
