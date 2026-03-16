<?php

namespace App\Http\Requests\StockAdjustmentInProductSerial;

use App\Helpers\HashidsHelper;
use App\Models\StockAdjustmentInProductSerial;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentInProductSerialStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', StockAdjustmentInProductSerial::class) ? true : false;
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'stock_adjustment_id' => ['required', 'integer', new ExistsForCompany('stock_adjustments', $this->company_id)],
            'stock_adjustment_in_product_id' => ['required', 'integer', new ExistsForCompany('stock_adjustment_in_products', $this->company_id)],
            'serial' => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.stock_adjustment_in_product_serial.company_id'),
            'branch_id' => trans('validation_attributes.stock_adjustment_in_product_serial.branch_id'),
            'stock_adjustment_id' => trans('validation_attributes.stock_adjustment_in_product_serial.stock_adjustment_id'),
            'stock_adjustment_in_product_id' => trans('validation_attributes.stock_adjustment_in_product_serial.stock_adjustment_in_product_id'),
            'serial' => trans('validation_attributes.stock_adjustment_in_product_serial.serial'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'stock_adjustment_id' => $this->filled('stock_adjustment_id') ? HashidsHelper::decodeId($this->stock_adjustment_id) : null,
            'stock_adjustment_in_product_id' => $this->filled('stock_adjustment_in_product_id') ? HashidsHelper::decodeId($this->stock_adjustment_in_product_id) : null,
        ]);
    }
}
