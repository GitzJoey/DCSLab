<?php

namespace App\Http\Requests\StockAdjustmentOutProduct;

use App\Helpers\HashidsHelper;
use App\Models\StockAdjustmentOutProduct;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentOutProductStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', StockAdjustmentOutProduct::class) ? true : false;
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'stock_adjustment_id' => ['required', 'integer', new ExistsForCompany('stock_adjustments', $this->company_id)],
            'qty' => ['required', 'numeric', 'min:1'],
            'product_unit_id' => ['required', 'integer', new ExistsForCompany('product_units', $this->company_id)],
            'product_unit_conversion_value' => ['required', 'numeric', 'min:1'],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],

            'serials' => ['present', 'nullable', 'array'],
            'serials.*.serial' => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.stock_adjustment_out_product.company_id'),
            'branch_id' => trans('validation_attributes.stock_adjustment_out_product.branch_id'),
            'stock_adjustment_id' => trans('validation_attributes.stock_adjustment_out_product.stock_adjustment_id'),
            'qty' => trans('validation_attributes.stock_adjustment_out_product.qty'),
            'product_unit_id' => trans('validation_attributes.stock_adjustment_out_product.product_unit_id'),
            'product_unit_conversion_value' => trans('validation_attributes.stock_adjustment_out_product.product_unit_conversion_value'),
            'remarks' => trans('validation_attributes.stock_adjustment_out_product.remarks'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'stock_adjustment_id' => $this->filled('stock_adjustment_id') ? HashidsHelper::decodeId($this->stock_adjustment_id) : null,
            'product_unit_id' => $this->filled('product_unit_id') ? HashidsHelper::decodeId($this->product_unit_id) : null,
        ]);
    }
}
