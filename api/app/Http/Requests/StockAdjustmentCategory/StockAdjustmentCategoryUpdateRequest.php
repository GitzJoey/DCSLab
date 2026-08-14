<?php

namespace App\Http\Requests\StockAdjustmentCategory;

use App\Helpers\HashidsHelper;
use App\Models\StockAdjustmentCategory;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentCategoryUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $stockAdjustmentCategory = $this->route('stock_adjustment_category');

        return $user->can('update', StockAdjustmentCategory::class, $stockAdjustmentCategory);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'code' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.stock_adjustment_category.company'),
            'code' => trans('validation_attributes.stock_adjustment_category.code'),
            'name' => trans('validation_attributes.stock_adjustment_category.name'),
        ];
    }

    public function validationData()
    {
        return $this->all();
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
        ]);
    }
}
