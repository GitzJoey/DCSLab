<?php

namespace App\Http\Requests\NonCapitalAddition;

use App\Helpers\HashidsHelper;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidCompany;
use App\Rules\IsValidNonCapitalAdditionCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class NonCapitalAdditionUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $nonCapitalAddition = $this->route('non_capital_addition');

        return $user->can('update', $nonCapitalAddition);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', 'bail', new IsValidBranch($this->company_id, true)],
            'code' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'category_id' => ['required', 'integer', new IsValidNonCapitalAdditionCategory($this->company_id)],
            'cash_account_id' => ['required', 'integer', new IsValidCashAccount($this->branch_id)],
            'amount' => ['required', 'numeric', 'min:0'],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.non_capital_addition.company'),
            'branch_id' => trans('validation_attributes.non_capital_addition.branch'),
            'code' => trans('validation_attributes.non_capital_addition.code'),
            'date' => trans('validation_attributes.non_capital_addition.date'),
            'category_id' => trans('validation_attributes.non_capital_addition.category'),
            'cash_account_id' => trans('validation_attributes.non_capital_addition.cash_account'),
            'amount' => trans('validation_attributes.non_capital_addition.amount'),
            'remarks' => trans('validation_attributes.non_capital_addition.remarks'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'category_id' => $this->filled('category_id') ? HashidsHelper::decodeId($this->category_id) : null,
            'cash_account_id' => $this->filled('cash_account_id') ? HashidsHelper::decodeId($this->cash_account_id) : null,
            'remarks' => $this->filled('remarks') ? $this['remarks'] : null,
        ]);
    }
}
