<?php

namespace App\Http\Requests\CapitalTransaction;

use App\Enums\CapitalTransactionTypeEnum;
use App\Helpers\HashidsHelper;
use App\Models\CapitalTransaction;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidCompany;
use App\Rules\IsValidInvestor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class CapitalTransactionStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', CapitalTransaction::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany],
            'branch_id' => ['required', 'integer', 'bail', new IsValidBranch($this->company_id, true)],
            'code' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'investor_id' => ['required', 'integer', 'bail', new IsValidInvestor($this->company_id)],
            'cash_account_id' => ['required', 'integer', 'bail', new IsValidCashAccount($this->branch_id)],
            'type' => ['required', new Enum(CapitalTransactionTypeEnum::class)],
            'amount' => ['required', 'numeric', 'min:0'],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.capital_transaction.company'),
            'branch_id' => trans('validation_attributes.capital_transaction.branch'),
            'code' => trans('validation_attributes.capital_transaction.code'),
            'date' => trans('validation_attributes.capital_transaction.date'),
            'investor_id' => trans('validation_attributes.capital_transaction.investor'),
            'cash_account_id' => trans('validation_attributes.capital_transaction.cash_account'),
            'type' => trans('validation_attributes.capital_transaction.type'),
            'amount' => trans('validation_attributes.capital_transaction.amount'),
            'remarks' => trans('validation_attributes.capital_transaction.remarks'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'investor_id' => $this->filled('investor_id') ? HashidsHelper::decodeId($this->investor_id) : null,
            'cash_account_id' => $this->filled('cash_account_id') ? HashidsHelper::decodeId($this->cash_account_id) : null,
            'type' => CapitalTransactionTypeEnum::isValid($this->type) ? CapitalTransactionTypeEnum::resolveToEnum($this->type)->value : null,
        ]);
    }
}
