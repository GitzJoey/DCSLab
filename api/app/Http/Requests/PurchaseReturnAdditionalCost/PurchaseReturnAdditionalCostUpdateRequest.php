<?php

namespace App\Http\Requests\PurchaseReturnAdditionalCost;

use App\Helpers\HashidsHelper;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidPurchase;
use App\Rules\IsValidPurchaseReturnAdditionalCostCategory;
use App\Rules\PurchaseReturnAdditionalCostUpdateValidCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseReturnAdditionalCostUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $purchaseReturnAdditionalCost = $this->route('purchase_return_additional_cost');

        return $user->can('update', $purchaseReturnAdditionalCost);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'purchase_id' => ['required', 'integer', new IsValidPurchase()],
            'code' => ['required', 'string', 'max:255', new PurchaseReturnAdditionalCostUpdateValidCode($this->company_id, $this->route('purchase_return_additional_cost'))],
            'date' => ['required', 'date'],
            'category_id' => ['required', 'integer', new IsValidPurchaseReturnAdditionalCostCategory()],
            'amount' => ['required', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.purchase_return_additional_cost.company'),
            'branch_id' => trans('validation_attributes.purchase_return_additional_cost.branch'),
            'purchase_id' => trans('validation_attributes.purchase_return_additional_cost.purchase'),
            'code' => trans('validation_attributes.purchase_return_additional_cost.code'),
            'date' => trans('validation_attributes.purchase_return_additional_cost.date'),
            'category_id' => trans('validation_attributes.purchase_return_additional_cost.category'),
            'amount' => trans('validation_attributes.purchase_return_additional_cost.amount'),
            'remarks' => trans('validation_attributes.purchase_return_additional_cost.remarks'),
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
