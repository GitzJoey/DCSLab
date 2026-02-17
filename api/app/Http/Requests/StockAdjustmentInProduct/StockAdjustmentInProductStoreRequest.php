<?php

namespace App\Http\Requests\StockAdjustmentInProduct;

use App\Helpers\HashidsHelper;
use App\Models\StockAdjustmentInProduct;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Validation\StockAdjustment\StockAdjustmentInProductRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentInProductStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', StockAdjustmentInProduct::class) ? true : false;
    }

    public function rules()
    {
        $rules = [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'stock_adjustment_id' => ['required', 'integer', new ExistsForCompany('stock_adjustments', $this->company_id)],
        ];

        $rules += StockAdjustmentInProductRules::mapToFieldNames($this->company_id ?? 0,
            'qty',
            'product_unit_id',
            'product_unit_conversion_value',
            'product_unit_cogs',
            'remarks',
        );

        return $rules;
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.stock_adjustment_in_product.company_id'),
            'branch_id' => trans('validation_attributes.stock_adjustment_in_product.branch_id'),
            'stock_adjustment_id' => trans('validation_attributes.stock_adjustment_in_product.stock_adjustment_id'),
            'qty' => trans('validation_attributes.stock_adjustment_in_product.qty'),
            'product_unit_id' => trans('validation_attributes.stock_adjustment_in_product.product_unit_id'),
            'product_unit_conversion_value' => trans('validation_attributes.stock_adjustment_in_product.product_unit_conversion_value'),
            'product_unit_cogs' => trans('validation_attributes.stock_adjustment_in_product.product_unit_cogs'),
            'remarks' => trans('validation_attributes.stock_adjustment_in_product.remarks'),
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
