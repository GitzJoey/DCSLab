<?php

namespace App\Http\Requests\NonCapitalWithdrawal;

use App\Helpers\HashidsHelper;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidCompany;
use App\Rules\IsValidNonCapitalWithdrawalCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class NonCapitalWithdrawalUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $nonCapitalWithdrawal = $this->route('non_capital_withdrawal');

        return $user->can('update', $nonCapitalWithdrawal);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', 'bail', new IsValidBranch($this->company_id, true)],
            'code' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'category_id' => ['required', 'integer', new IsValidNonCapitalWithdrawalCategory($this->company_id)],
            'cash_account_id' => ['required', 'integer', new IsValidCashAccount($this->company_id)],
            'amount' => ['required', 'numeric', 'min:0'],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.non_capital_withdrawal.company'),
            'branch_id' => trans('validation_attributes.non_capital_withdrawal.branch'),
            'code' => trans('validation_attributes.non_capital_withdrawal.code'),
            'date' => trans('validation_attributes.non_capital_withdrawal.date'),
            'category_id' => trans('validation_attributes.non_capital_withdrawal.category'),
            'cash_account_id' => trans('validation_attributes.non_capital_withdrawal.cash_account'),
            'amount' => trans('validation_attributes.non_capital_withdrawal.amount'),
            'remarks' => trans('validation_attributes.non_capital_withdrawal.remarks'),
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
