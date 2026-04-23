<?php

namespace App\Http\Requests\LiabilityPayment;

use App\Helpers\HashidsHelper;
use App\Models\LiabilityPayment;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class LiabilityPaymentUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $liabilityPayment = $this->route('liability_payment');

        return $user->can('update', $liabilityPayment);
    }

    public function prepareForValidation()
    {
        $liabilityPayment = $this->route('liability_payment');

        $this->merge([
            'company_id' => $liabilityPayment->company_id,
            'branch_id' => $liabilityPayment->branch_id,
            'liability_id' => $liabilityPayment->liability_id,
            'cash_account_id' => $this->filled('cash_account_id') ? HashidsHelper::decodeId($this->cash_account_id) : null,
        ]);
    }

    public function rules()
    {
        /** @var LiabilityPayment $liabilityPayment */
        $liabilityPayment = $this->route('liability_payment');

        return [
            'company_id' => ['required', 'integer'],
            'branch_id' => ['required', 'integer'],
            'liability_id' => ['required', 'integer', new ExistsForCompany('liabilities', $this->company_id)],
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
                function (string $attribute, mixed $value, \Closure $fail) use ($liabilityPayment): void {
                    $liability = $liabilityPayment->liability;
                    if (! $liability || is_null($value)) {
                        return;
                    }

                    $maxAmount = (float) $liability->amount_due + (float) $liabilityPayment->amount;

                    if ((float) $value > $maxAmount) {
                        $fail(trans('validation.max.numeric', [
                            'attribute' => trans('validation_attributes.liability_payment.amount'),
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
        return trans('validation_attributes.liability_payment');
    }
}
