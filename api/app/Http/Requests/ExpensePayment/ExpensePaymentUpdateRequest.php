<?php

namespace App\Http\Requests\ExpensePayment;

use App\Helpers\HashidsHelper;
use App\Models\ExpensePayment;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ExpensePaymentUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $expensePayment = $this->route('expense_payment');

        return $user->can('update', $expensePayment);
    }

    public function prepareForValidation()
    {
        $expensePayment = $this->route('expense_payment');

        $this->merge([
            'company_id' => $expensePayment->company_id,
            'branch_id' => $expensePayment->branch_id,
            'expense_id' => $expensePayment->expense_id,
            'cash_account_id' => $this->filled('cash_account_id') ? HashidsHelper::decodeId($this->cash_account_id) : null,
        ]);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer'],
            'branch_id' => ['required', 'integer'],
            'expense_id' => ['required', 'integer', new ExistsForCompany('expenses', $this->company_id)],
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
            /** @var ExpensePayment $payment */
            $payment = $this->route('expense_payment');
            $expense = $payment->expense;

            if (! $expense) {
                return;
            }

            $maxAmount = (float) $expense->amount_payable_due + (float) $payment->amount;
            if ((float) $this->input('amount', 0) > $maxAmount) {
                $validator->errors()->add('amount', trans('validation.max.numeric', [
                    'attribute' => trans('validation_attributes.expense_payment.amount'),
                    'max' => $maxAmount,
                ]));
            }
        });
    }

    public function attributes()
    {
        return trans('validation_attributes.expense_payment');
    }
}
