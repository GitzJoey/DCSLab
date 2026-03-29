<?php

namespace App\Http\Requests\SaleProductUnitSerial;

use App\Helpers\HashidsHelper;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidSale;
use App\Rules\IsValidSaleProductUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SaleProductUnitSerialUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $saleProductUnitSerial = $this->route('sale_product_unit_serial');

        return $user->can('update', $saleProductUnitSerial);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'sale_id' => ['required', 'integer', new IsValidSale()],
            'sale_product_unit_id' => ['required', 'integer', 'bail', new IsValidSaleProductUnit()],
            'serial' => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.sale_product_unit_serial.company'),
            'branch_id' => trans('validation_attributes.sale_product_unit_serial.branch'),
            'sale_id' => trans('validation_attributes.sale_product_unit_serial.sale'),
            'sale_product_unit_id' => trans('validation_attributes.sale_product_unit_serial.sale_product_unit'),
            'serial' => trans('validation_attributes.sale_product_unit_serial.serial'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
        ]);
    }
}
