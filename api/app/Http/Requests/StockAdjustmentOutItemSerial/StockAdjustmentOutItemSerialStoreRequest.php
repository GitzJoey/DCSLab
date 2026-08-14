<?php

namespace App\Http\Requests\StockAdjustmentOutItemSerial;

use App\Helpers\HashidsHelper;
use App\Models\StockAdjustmentOutItemSerial;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidStockAdjustmentOutItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentOutItemSerialStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', StockAdjustmentOutItemSerial::class) ? true : false;
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'stock_adjustment_id' => ['required', 'integer', new ExistsForCompany('stock_adjustments', $this->company_id)],
            'stock_adjustment_out_item_id' => ['required', 'integer', new IsValidStockAdjustmentOutItem($this->company_id, $this->stock_adjustment_id)],
            'serial' => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.stock_adjustment_out_item_serial.company_id'),
            'branch_id' => trans('validation_attributes.stock_adjustment_out_item_serial.branch_id'),
            'stock_adjustment_id' => trans('validation_attributes.stock_adjustment_out_item_serial.stock_adjustment_id'),
            'stock_adjustment_out_item_id' => trans('validation_attributes.stock_adjustment_out_item_serial.stock_adjustment_out_item_id'),
            'serial' => trans('validation_attributes.stock_adjustment_out_item_serial.serial'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'stock_adjustment_id' => $this->filled('stock_adjustment_id') ? HashidsHelper::decodeId($this->stock_adjustment_id) : null,
            'stock_adjustment_out_item_id' => $this->filled('stock_adjustment_out_item_id') ? HashidsHelper::decodeId($this->stock_adjustment_out_item_id) : null,
        ]);
    }
}
