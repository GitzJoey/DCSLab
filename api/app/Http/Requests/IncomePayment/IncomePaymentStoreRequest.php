<?php

namespace App\Http\Requests\IncomePayment;

use App\Helpers\HashidsHelper;
use App\Models\Income;
use App\Models\IncomePayment;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidCompany;
use App\Rules\IsValidDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class IncomePaymentStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', IncomePayment::class);
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'income_id' => $this->filled('income_id') ? HashidsHelper::decodeId($this->income_id) : null,
            'cash_account_id' => $this->filled('cash_account_id') ? HashidsHelper::decodeId($this->cash_account_id) : null,
        ]);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', 'bail', new IsValidBranch($this->company_id, true)],
            'income_id' => ['required', 'integer', new ExistsForCompany('incomes', $this->company_id)],
            'code' => ['required', 'string', 'max:255'],
            'date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'cash_account_id' => [
                'required',
                'integer',
                'bail',
                new ExistsForCompany('cash_accounts', $this->company_id),
                new IsValidCashAccount($this->branch_id),
            ],
            'amount' => ['required', 'numeric', 'gt:0'],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $income = Income::find($this->input('income_id'));
            if (! $income) {
                return;
            }

            if ((float) $this->input('amount', 0) > (float) $income->amount_receivable_due) {
                $validator->errors()->add('amount', trans('validation.max.numeric', [
                    'attribute' => trans('validation_attributes.income_payment.amount'),
                    'max' => $income->amount_receivable_due,
                ]));
            }
        });
    }

    public function attributes()
    {
        return trans('validation_attributes.income_payment');
    }
}
