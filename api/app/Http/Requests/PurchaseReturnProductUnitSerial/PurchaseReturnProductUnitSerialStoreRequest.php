<?php

namespace App\Http\Requests\PurchaseReturnProductUnitSerial;

use App\Helpers\HashidsHelper;
use App\Models\PurchaseReturnProductUnitSerial;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidPurchase;
use App\Rules\IsValidPurchaseReturnProductUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseReturnProductUnitSerialStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', PurchaseReturnProductUnitSerial::class);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'purchase_id' => ['required', 'integer', new IsValidPurchase()],
            'purchase_return_product_unit_id' => ['required', 'integer', new IsValidPurchaseReturnProductUnit()],
            'serial' => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.purchase_return_product_unit_serial.company'),
            'branch_id' => trans('validation_attributes.purchase_return_product_unit_serial.branch'),
            'purchase_id' => trans('validation_attributes.purchase_return_product_unit_serial.purchase'),
            'purchase_return_product_unit_id' => trans('validation_attributes.purchase_return_product_unit_serial.purchase_return_product_unit'),
            'serial' => trans('validation_attributes.purchase_return_product_unit_serial.serial'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'remarks' => $this->filled('remarks') ? $this['remarks'] : null,
        ]);
    }
}
