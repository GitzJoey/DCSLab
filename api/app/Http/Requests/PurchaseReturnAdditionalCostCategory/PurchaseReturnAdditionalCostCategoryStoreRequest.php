<?php

namespace App\Http\Requests\PurchaseReturnAdditionalCostCategory;

use App\Helpers\HashidsHelper;
use App\Models\PurchaseReturnAdditionalCostCategory;
use App\Rules\IsValidCompany;
use App\Rules\PurchaseReturnAdditionalCostCategoryStoreValidCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseReturnAdditionalCostCategoryStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', PurchaseReturnAdditionalCostCategory::class);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'code' => ['required', 'string', 'max:255', new PurchaseReturnAdditionalCostCategoryStoreValidCode($this->company_id)],
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.purchase_return_additional_cost_category.company'),
            'code' => trans('validation_attributes.purchase_return_additional_cost_category.code'),
            'remarks' => trans('validation_attributes.purchase_return_additional_cost_category.remarks'),
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
