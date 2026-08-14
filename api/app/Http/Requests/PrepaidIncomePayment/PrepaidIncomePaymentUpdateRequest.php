<?php

namespace App\Http\Requests\PrepaidIncomePayment;

use App\Helpers\HashidsHelper;
use App\Models\PrepaidIncomePayment;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PrepaidIncomePaymentUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $prepaidIncomePayment = $this->route('prepaid_income_payment');

        return $user->can('update', $prepaidIncomePayment);
    }

    public function prepareForValidation()
    {
        $prepaidIncomePayment = $this->route('prepaid_income_payment');

        $this->merge([
            'company_id' => $prepaidIncomePayment->company_id,
            'branch_id' => $prepaidIncomePayment->branch_id,
            'prepaid_income_id' => $prepaidIncomePayment->prepaid_income_id,
            'cash_account_id' => $this->filled('cash_account_id') ? HashidsHelper::decodeId($this->cash_account_id) : null,
        ]);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer'],
            'branch_id' => ['required', 'integer'],
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
            /** @var PrepaidIncomePayment $payment */
            $payment = $this->route('prepaid_income_payment');
            $prepaidIncome = $payment->prepaidIncome;

            if (! $prepaidIncome) {
                return;
            }

            $maxAmount = (float) $prepaidIncome->amount_receivable_due + (float) $payment->amount;
            if ((float) $this->input('amount', 0) > $maxAmount) {
                $validator->errors()->add('amount', trans('validation.max.numeric', [
                    'attribute' => trans('validation_attributes.prepaid_income_payment.amount'),
                    'max' => $maxAmount,
                ]));
            }
        });
    }

    public function attributes()
    {
        return trans('validation_attributes.prepaid_income_payment');
    }
}
