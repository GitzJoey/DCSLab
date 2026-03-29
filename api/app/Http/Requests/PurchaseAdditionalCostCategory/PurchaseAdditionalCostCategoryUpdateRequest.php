<?php

namespace App\Http\Requests\PurchaseAdditionalCostCategory;

use App\Helpers\HashidsHelper;
use App\Models\PurchaseAdditionalCostCategory;
use App\Rules\IsValidCompany;
use App\Rules\PurchaseAdditionalCostCategoryUpdateValidCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseAdditionalCostCategoryUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $purchaseAdditionalCostCategory = $this->route('purchase_additional_cost_category');

        return $user->can('update', PurchaseAdditionalCostCategory::class, $purchaseAdditionalCostCategory) ? true : false;
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'code' => ['required', 'string', 'max:255', new PurchaseAdditionalCostCategoryUpdateValidCode($this->company_id, $this->route('purchase_additional_cost_category'))],
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.purchase_additional_cost_category.company'),
            'code' => trans('validation_attributes.purchase_additional_cost_category.code'),
            'name' => trans('validation_attributes.purchase_additional_cost_category.name'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
        ]);
    }
}
