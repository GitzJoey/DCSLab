<?php

namespace App\Http\Requests\ReceivablePayment;

use App\Helpers\HashidsHelper;
use App\Models\ReceivablePayment;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ReceivablePaymentUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $receivablePayment = $this->route('receivable_payment');

        return $user->can('update', $receivablePayment);
    }

    public function prepareForValidation()
    {
        $receivablePayment = $this->route('receivable_payment');

        $this->merge([
            'company_id' => $receivablePayment->company_id,
            'branch_id' => $receivablePayment->branch_id,
            'receivable_id' => $receivablePayment->receivable_id,
            'cash_account_id' => $this->filled('cash_account_id') ? HashidsHelper::decodeId($this->cash_account_id) : null,
        ]);
    }

    public function rules()
    {
        /** @var ReceivablePayment $receivablePayment */
        $receivablePayment = $this->route('receivable_payment');

        return [
            'company_id' => ['required', 'integer'],
            'branch_id' => ['required', 'integer'],
            'receivable_id' => ['required', 'integer', new ExistsForCompany('receivables', $this->company_id)],
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
                function (string $attribute, mixed $value, \Closure $fail) use ($receivablePayment): void {
                    $receivable = $receivablePayment->receivable;
                    if (! $receivable || is_null($value)) {
                        return;
                    }

                    $maxAmount = (float) $receivable->amount_due + (float) $receivablePayment->amount;

                    if ((float) $value > $maxAmount) {
                        $fail(trans('validation.max.numeric', [
                            'attribute' => trans('validation_attributes.receivable_payment.amount'),
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
        return trans('validation_attributes.receivable_payment');
    }
}
