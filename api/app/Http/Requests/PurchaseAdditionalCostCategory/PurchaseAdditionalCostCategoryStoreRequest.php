<?php

namespace App\Http\Requests\PurchaseAdditionalCostCategory;

use App\Helpers\HashidsHelper;
use App\Models\PurchaseAdditionalCostCategory;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseAdditionalCostCategoryStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', PurchaseAdditionalCostCategory::class);
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
        ]);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'code' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return trans('validation_attributes.purchase_additional_cost_category');
    }
}
