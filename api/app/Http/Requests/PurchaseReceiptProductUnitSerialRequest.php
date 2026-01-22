<?php

namespace App\Http\Requests;

use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Models\PurchaseReceiptProductUnitSerial;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidPurchaseReceipt;
use App\Rules\IsValidPurchaseReceiptProductUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseReceiptProductUnitSerialRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $purchaseReceiptProductUnitSerial = $this->route('purchase_receipt_product_unit_serial');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                return $user->can('viewAny', PurchaseReceiptProductUnitSerial::class) ? true : false;
            case 'read':
                return $user->can('view', PurchaseReceiptProductUnitSerial::class, $purchaseReceiptProductUnitSerial) ? true : false;
            case 'store':
                return $user->can('create', PurchaseReceiptProductUnitSerial::class) ? true : false;
            case 'update':
                return $user->can('update', PurchaseReceiptProductUnitSerial::class, $purchaseReceiptProductUnitSerial) ? true : false;
            case 'delete':
                return $user->can('delete', PurchaseReceiptProductUnitSerial::class, $purchaseReceiptProductUnitSerial) ? true : false;
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
                    'purchase_receipt_product_unit_id' => ['required', 'integer', new IsValidPurchaseReceiptProductUnit($this->company_id)],
                    'serial' => ['required', 'string', 'max:255'],
                ];
            case 'update':
                return [
                    'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
                    'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
                    'purchase_receipt_id' => ['required', 'integer', new IsValidPurchaseReceipt($this->company_id)],
                    'purchase_receipt_product_unit_id' => ['required', 'integer', new IsValidPurchaseReceiptProductUnit($this->company_id)],
                    'serial' => ['required', 'string', 'max:255'],
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
            'company_id' => trans('validation_attributes.purchase_receipt_product_unit_serial.company'),
            'branch_id' => trans('validation_attributes.purchase_receipt_product_unit_serial.branch'),
            'purchase_receipt_id' => trans('validation_attributes.purchase_receipt_product_unit_serial.purchase_receipt'),
            'purchase_receipt_product_unit_id' => trans('validation_attributes.purchase_receipt_product_unit_serial.purchase_receipt_product_unit'),
            'serial' => trans('validation_attributes.purchase_receipt_product_unit_serial.serial'),
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
