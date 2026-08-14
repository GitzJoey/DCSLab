<?php

namespace App\Http\Requests\CashTransfer;

use App\Helpers\HashidsHelper;
use App\Models\CashTransfer;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidCompany;
use App\Rules\IsValidDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CashTransferStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', CashTransfer::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany],
            'branch_id' => ['required', 'integer', 'bail', new IsValidBranch($this->company_id, true)],
            'code' => ['required', 'string', 'max:255'],
            'date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'source_cash_account_id' => ['required', 'integer', 'bail', new IsValidCashAccount($this->branch_id), 'different:destination_cash_account_id'],
            'destination_cash_account_id' => ['required', 'integer', 'bail', new IsValidCashAccount($this->branch_id), 'different:source_cash_account_id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.cash_transfer.company'),
            'branch_id' => trans('validation_attributes.cash_transfer.branch'),
            'code' => trans('validation_attributes.cash_transfer.code'),
            'date' => trans('validation_attributes.cash_transfer.date'),
            'source_cash_account_id' => trans('validation_attributes.cash_transfer.source_cash_account'),
            'destination_cash_account_id' => trans('validation_attributes.cash_transfer.destination_cash_account'),
            'amount' => trans('validation_attributes.cash_transfer.amount'),
            'remarks' => trans('validation_attributes.cash_transfer.remarks'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'source_cash_account_id' => $this->filled('source_cash_account_id') ? HashidsHelper::decodeId($this->source_cash_account_id) : null,
            'destination_cash_account_id' => $this->filled('destination_cash_account_id') ? HashidsHelper::decodeId($this->destination_cash_account_id) : null,
        ]);
    }
}
