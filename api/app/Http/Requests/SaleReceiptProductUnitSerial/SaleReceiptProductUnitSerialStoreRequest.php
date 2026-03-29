<?php

namespace App\Http\Requests\SaleReceiptProductUnitSerial;

use App\Helpers\HashidsHelper;
use App\Models\SaleReceiptProductUnitSerial;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidSaleReceipt;
use App\Rules\IsValidSaleReceiptProductUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SaleReceiptProductUnitSerialStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', SaleReceiptProductUnitSerial::class);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', 'bail', new IsValidBranch($this->company_id, true)],
            'sale_receipt_id' => ['required', 'integer', 'bail', new IsValidSaleReceipt($this->company_id)],
            'sale_receipt_product_unit_id' => ['required', 'integer', 'bail', new IsValidSaleReceiptProductUnit($this->company_id)],
            'serial' => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.sale_receipt_product_unit_serial.company'),
            'branch_id' => trans('validation_attributes.sale_receipt_product_unit_serial.branch'),
            'sale_receipt_id' => trans('validation_attributes.sale_receipt_product_unit_serial.sale_receipt'),
            'sale_receipt_product_unit_id' => trans('validation_attributes.sale_receipt_product_unit_serial.sale_receipt_product_unit'),
            'serial' => trans('validation_attributes.sale_receipt_product_unit_serial.serial'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
        ]);
    }
}
