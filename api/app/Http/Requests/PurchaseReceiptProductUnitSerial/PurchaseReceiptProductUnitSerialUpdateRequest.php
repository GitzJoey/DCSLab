<?php

namespace App\Http\Requests\PurchaseReceiptProductUnitSerial;

use App\Helpers\HashidsHelper;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidPurchaseReceipt;
use App\Rules\IsValidPurchaseReceiptProductUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseReceiptProductUnitSerialUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $purchaseReceiptProductUnitSerial = $this->route('purchase_receipt_product_unit_serial');

        return $user->can('update', $purchaseReceiptProductUnitSerial);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'purchase_receipt_id' => ['required', 'integer', new IsValidPurchaseReceipt($this->company_id)],
            'purchase_receipt_product_unit_id' => ['required', 'integer', new IsValidPurchaseReceiptProductUnit($this->company_id)],
            'serial' => ['required', 'string', 'max:255'],
        ];
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

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
        ]);
    }
}
