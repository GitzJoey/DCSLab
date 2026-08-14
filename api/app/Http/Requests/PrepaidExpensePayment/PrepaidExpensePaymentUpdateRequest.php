<?php

namespace App\Http\Requests\PrepaidExpensePayment;

use App\Helpers\HashidsHelper;
use App\Models\PrepaidExpensePayment;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PrepaidExpensePaymentUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $prepaidExpensePayment = $this->route('prepaid_expense_payment');

        return $user->can('update', $prepaidExpensePayment);
    }

    public function prepareForValidation()
    {
        $prepaidExpensePayment = $this->route('prepaid_expense_payment');

        $this->merge([
            'company_id' => $prepaidExpensePayment->company_id,
            'branch_id' => $prepaidExpensePayment->branch_id,
            'prepaid_expense_id' => $prepaidExpensePayment->prepaid_expense_id,
            'cash_account_id' => $this->filled('cash_account_id') ? HashidsHelper::decodeId($this->cash_account_id) : null,
        ]);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer'],
            'branch_id' => ['required', 'integer'],
            'prepaid_expense_id' => ['required', 'integer', new ExistsForCompany('prepaid_expenses', $this->company_id)],
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
            /** @var PrepaidExpensePayment $payment */
            $payment = $this->route('prepaid_expense_payment');
            $prepaidExpense = $payment->prepaidExpense;

            if (! $prepaidExpense) {
                return;
            }

            $maxAmount = (float) $prepaidExpense->amount_payable_due + (float) $payment->amount;
            if ((float) $this->input('amount', 0) > $maxAmount) {
                $validator->errors()->add('amount', trans('validation.max.numeric', [
                    'attribute' => trans('validation_attributes.prepaid_expense_payment.amount'),
                    'max' => $maxAmount,
                ]));
            }
        });
    }

    public function attributes()
    {
        return trans('validation_attributes.prepaid_expense_payment');
    }
}
