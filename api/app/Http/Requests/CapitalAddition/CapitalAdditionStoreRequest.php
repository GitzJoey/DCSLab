<?php

namespace App\Http\Requests\CapitalAddition;

use App\Helpers\HashidsHelper;
use App\Models\CapitalAddition;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidCompany;
use App\Rules\IsValidInvestor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CapitalAdditionStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', CapitalAddition::class);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'code' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'investor_id' => ['required', 'integer', new IsValidInvestor($this->company_id)],
            'cash_account_id' => ['required', 'integer', new IsValidCashAccount($this->branch_id)],
            'amount' => ['required', 'numeric', 'min:0'],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.capital_addition.company'),
            'code' => trans('validation_attributes.capital_addition.code'),
            'date' => trans('validation_attributes.capital_addition.date'),
            'investor_id' => trans('validation_attributes.capital_addition.investor'),
            'cash_account_id' => trans('validation_attributes.capital_addition.cash_account'),
            'amount' => trans('validation_attributes.capital_addition.amount'),
            'remarks' => trans('validation_attributes.capital_addition.remarks'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'investor_id' => $this->filled('investor_id') ? HashidsHelper::decodeId($this->investor_id) : null,
            'cash_account_id' => $this->filled('cash_account_id') ? HashidsHelper::decodeId($this->cash_account_id) : null,
            'remarks' => $this->filled('remarks') ? $this['remarks'] : null,
        ]);
    }
}
