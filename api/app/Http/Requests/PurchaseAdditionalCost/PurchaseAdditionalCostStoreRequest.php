<?php

namespace App\Http\Requests\PurchaseAdditionalCost;

use App\Helpers\HashidsHelper;
use App\Models\PurchaseAdditionalCost;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidPurchase;
use App\Rules\IsValidPurchaseAdditionalCostCategory;
use App\Rules\PurchaseAdditionalCostStoreValidCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseAdditionalCostStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', PurchaseAdditionalCost::class) ? true : false;
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'purchase_id' => ['required', 'integer', new IsValidPurchase($this->company_id, true)],
            'code' => ['required', 'string', 'max:255', new PurchaseAdditionalCostStoreValidCode($this->company_id)],
            'date' => ['required', 'date'],
            'category_id' => ['required', 'integer', new IsValidPurchaseAdditionalCostCategory()],
            'amount' => ['required', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.purchase_additional_cost.company'),
            'branch_id' => trans('validation_attributes.purchase_additional_cost.branch'),
            'purchase_id' => trans('validation_attributes.purchase_additional_cost.purchase'),
            'code' => trans('validation_attributes.purchase_additional_cost.code'),
            'date' => trans('validation_attributes.purchase_additional_cost.date'),
            'category_id' => trans('validation_attributes.purchase_additional_cost.category'),
            'amount' => trans('validation_attributes.purchase_additional_cost.amount'),
            'remarks' => trans('validation_attributes.purchase_additional_cost.remarks'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'purchase_id' => $this->filled('purchase_id') ? HashidsHelper::decodeId($this->purchase_id) : null,
            'category_id' => $this->filled('category_id') ? HashidsHelper::decodeId($this->category_id) : null,
            'remarks' => $this->filled('remarks') ? $this->remarks : null,
        ]);
    }
}
