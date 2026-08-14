<?php

namespace App\Http\Requests\ReceivablePayment;

use App\Helpers\HashidsHelper;
use App\Models\Receivable;
use App\Models\ReceivablePayment;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidCompany;
use App\Rules\IsValidDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ReceivablePaymentStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', ReceivablePayment::class);
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'receivable_id' => $this->filled('receivable_id') ? HashidsHelper::decodeId($this->receivable_id) : null,
            'cash_account_id' => $this->filled('cash_account_id') ? HashidsHelper::decodeId($this->cash_account_id) : null,
        ]);
    }

    public function rules()
    {
        $receivable = $this->filled('receivable_id')
            ? Receivable::find($this->input('receivable_id'))
            : null;

        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', 'bail', new IsValidBranch($this->company_id, true)],
            'receivable_id' => [
                'required',
                'integer',
                Rule::exists('receivables', 'id')->where(function ($query) {
                    $query->where('company_id', $this->company_id)
                        ->where('branch_id', $this->branch_id)
                        ->whereNull('deleted_at');
                }),
            ],
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
                function (string $attribute, mixed $value, \Closure $fail) use ($receivable): void {
                    if (! $receivable || is_null($value)) {
                        return;
                    }

                    $maxAmount = (float) $receivable->amount_due;

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

    public function messages()
    {
        return [
            'receivable_id.exists' => trans('rules.receivable_payment.invalid_receivable_reference'),
        ];
    }
}
