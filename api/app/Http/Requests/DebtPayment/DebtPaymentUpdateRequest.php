<?php

namespace App\Http\Requests\DebtPayment;

use App\Helpers\HashidsHelper;
use App\Models\DebtPayment;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DebtPaymentUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $debtPayment = $this->route('debt_payment');

        return $user->can('update', $debtPayment);
    }

    public function prepareForValidation()
    {
        $debtPayment = $this->route('debt_payment');

        $this->merge([
            'company_id' => $debtPayment->company_id,
            'branch_id' => $debtPayment->branch_id,
            'debt_id' => $debtPayment->debt_id,
            'cash_account_id' => $this->filled('cash_account_id') ? HashidsHelper::decodeId($this->cash_account_id) : null,
        ]);
    }

    public function rules()
    {
        /** @var DebtPayment $debtPayment */
        $debtPayment = $this->route('debt_payment');

        return [
            'company_id' => ['required', 'integer'],
            'branch_id' => ['required', 'integer'],
            'debt_id' => ['required', 'integer', new ExistsForCompany('debts', $this->company_id)],
            'code' => ['required', 'string', 'max:255'],
            'date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'cash_account_id' => [
                'required',
                'integer',
                'bail',
                new ExistsForCompany('cash_accounts', $this->company_id),
                new IsValidCashAccount($this->branch_id),
            ],
            'amount' => [
                'required',
                'numeric',
                'gt:0',
                function (string $attribute, mixed $value, \Closure $fail) use ($debtPayment): void {
                    $debt = $debtPayment->debt;
                    if (! $debt || is_null($value)) {
                        return;
                    }

                    $maxAmount = (float) $debt->amount_due + (float) $debtPayment->amount;

                    if ((float) $value > $maxAmount) {
                        $fail(trans('validation.max.numeric', [
                            'attribute' => trans('validation_attributes.debt_payment.amount'),
                            'max' => $maxAmount,
                        ]));
                    }
                },
            ],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return trans('validation_attributes.debt_payment');
    }
}
