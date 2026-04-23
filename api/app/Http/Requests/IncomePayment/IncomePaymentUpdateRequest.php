<?php

namespace App\Http\Requests\IncomePayment;

use App\Helpers\HashidsHelper;
use App\Models\IncomePayment;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class IncomePaymentUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $incomePayment = $this->route('income_payment');

        return $user->can('update', $incomePayment);
    }

    public function prepareForValidation()
    {
        $incomePayment = $this->route('income_payment');

        $this->merge([
            'company_id' => $incomePayment->company_id,
            'branch_id' => $incomePayment->branch_id,
            'income_id' => $incomePayment->income_id,
            'cash_account_id' => $this->filled('cash_account_id') ? HashidsHelper::decodeId($this->cash_account_id) : null,
        ]);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer'],
            'branch_id' => ['required', 'integer'],
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
            /** @var IncomePayment $payment */
            $payment = $this->route('income_payment');
            $income = $payment->income;

            if (! $income) {
                return;
            }

            $maxAmount = (float) $income->amount_receivable_due + (float) $payment->amount;
            if ((float) $this->input('amount', 0) > $maxAmount) {
                $validator->errors()->add('amount', trans('validation.max.numeric', [
                    'attribute' => trans('validation_attributes.income_payment.amount'),
                    'max' => $maxAmount,
                ]));
            }
        });
    }

    public function attributes()
    {
        return trans('validation_attributes.income_payment');
    }
}
