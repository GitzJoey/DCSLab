<?php

namespace App\Http\Requests\StockTransferProductUnitSerial;

use App\Helpers\HashidsHelper;
use App\Models\StockTransferProductUnitSerial;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidStockTransfer;
use App\Rules\IsValidStockTransferProductUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StockTransferProductUnitSerialStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', StockTransferProductUnitSerial::class) ? true : false;
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'stock_transfer_id' => ['required', 'integer', new IsValidStockTransfer()],
            'stock_transfer_product_unit_id' => ['required', 'integer', new IsValidStockTransferProductUnit()],
            'serial' => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.stock_transfer_product_unit_serial.company'),
            'branch_id' => trans('validation_attributes.stock_transfer_product_unit_serial.branch'),
            'stock_transfer_id' => trans('validation_attributes.stock_transfer_product_unit_serial.stock_transfer'),
            'stock_transfer_product_unit_id' => trans('validation_attributes.stock_transfer_product_unit_serial.stock_transfer_product_unit'),
            'serial' => trans('validation_attributes.stock_transfer_product_unit_serial.serial'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'stock_transfer_id' => $this->filled('stock_transfer_id') ? HashidsHelper::decodeId($this->stock_transfer_id) : null,
            'stock_transfer_product_unit_id' => $this->filled('stock_transfer_product_unit_id') ? HashidsHelper::decodeId($this->stock_transfer_product_unit_id) : null,
        ]);
    }
}
