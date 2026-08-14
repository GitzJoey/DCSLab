<?php

namespace App\Http\Requests\PrepaidIncomePayment;

use App\Helpers\HashidsHelper;
use App\Models\PrepaidIncome;
use App\Models\PrepaidIncomePayment;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidCompany;
use App\Rules\IsValidDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PrepaidIncomePaymentStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', PrepaidIncomePayment::class);
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'prepaid_income_id' => $this->filled('prepaid_income_id') ? HashidsHelper::decodeId($this->prepaid_income_id) : null,
            'cash_account_id' => $this->filled('cash_account_id') ? HashidsHelper::decodeId($this->cash_account_id) : null,
        ]);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', 'bail', new IsValidBranch($this->company_id, true)],
            'prepaid_income_id' => ['required', 'integer', new ExistsForCompany('prepaid_incomes', $this->company_id)],
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
            $prepaidIncome = PrepaidIncome::find($this->input('prepaid_income_id'));
            if (! $prepaidIncome) {
                return;
            }

            if ((float) $this->input('amount', 0) > (float) $prepaidIncome->amount_receivable_due) {
                $validator->errors()->add('amount', trans('validation.max.numeric', [
                    'attribute' => trans('validation_attributes.prepaid_income_payment.amount'),
                    'max' => $prepaidIncome->amount_receivable_due,
                ]));
            }
        });
    }

    public function attributes()
    {
        return trans('validation_attributes.prepaid_income_payment');
    }
}
