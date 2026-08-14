<?php

namespace App\Http\Requests\StockTransferItemSerial;

use App\Helpers\HashidsHelper;
use App\Models\StockTransferItemSerial;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidStockTransfer;
use App\Rules\IsValidStockTransferItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StockTransferItemSerialStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', StockTransferItemSerial::class) ? true : false;
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'stock_transfer_id' => ['required', 'integer', new IsValidStockTransfer($this->company_id)],
            'stock_transfer_item_id' => ['required', 'integer', new IsValidStockTransferItem($this->company_id, $this->stock_transfer_id)],
            'serial' => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.stock_transfer_item_serial.company'),
            'branch_id' => trans('validation_attributes.stock_transfer_item_serial.branch'),
            'stock_transfer_id' => trans('validation_attributes.stock_transfer_item_serial.stock_transfer'),
            'stock_transfer_item_id' => trans('validation_attributes.stock_transfer_item_serial.stock_transfer_item'),
            'serial' => trans('validation_attributes.stock_transfer_item_serial.serial'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'stock_transfer_id' => $this->filled('stock_transfer_id') ? HashidsHelper::decodeId($this->stock_transfer_id) : null,
            'stock_transfer_item_id' => $this->filled('stock_transfer_item_id') ? HashidsHelper::decodeId($this->stock_transfer_item_id) : null,
        ]);
    }
}
