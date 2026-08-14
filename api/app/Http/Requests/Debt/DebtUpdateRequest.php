<?php

namespace App\Http\Requests\Debt;

use App\Helpers\HashidsHelper;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DebtUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $debt = $this->route('debt');

        return $user->can('update', $debt);
    }

    public function prepareForValidation()
    {
        $debt = $this->route('debt');
        $deletePaymentIds = collect($this->input('delete_payment_ids', []))
            ->map(fn ($id) => HashidsHelper::decodeId($id))
            ->all();

        $this->merge([
            'company_id' => $debt->company_id,
            'branch_id' => $debt->branch_id,
            'category_id' => $this->filled('category_id') ? HashidsHelper::decodeId($this->category_id) : null,
            'creditor_id' => $this->filled('creditor_id') ? HashidsHelper::decodeId($this->creditor_id) : null,
            'supplier_id' => $this->filled('supplier_id') ? HashidsHelper::decodeId($this->supplier_id) : null,
            'cash_account_id' => $this->filled('cash_account_id') ? HashidsHelper::decodeId($this->cash_account_id) : null,
            'delete_payment_ids' => $deletePaymentIds,
        ]);

        if (is_array($this->input('payments'))) {
            $payments = [];
            foreach ($this->input('payments') as $payment) {
                if (is_array($payment) && array_key_exists('id', $payment) && ! is_null($payment['id'])) {
                    $payment['id'] = HashidsHelper::decodeId($payment['id']);
                }

                if (is_array($payment) && array_key_exists('cash_account_id', $payment) && ! is_null($payment['cash_account_id'])) {
                    $payment['cash_account_id'] = HashidsHelper::decodeId($payment['cash_account_id']);
                }

                $payments[] = $payment;
            }

            $this->merge(['payments' => $payments]);
        }
    }

    public function rules()
    {
        $debt = $this->route('debt');

        return [
            'company_id' => ['required', 'integer'],
            'branch_id' => ['required', 'integer'],
            'code' => ['required', 'string', 'max:255'],
            'date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'category_id' => ['required', 'integer', new ExistsForCompany('debt_categories', $this->company_id)],
            'creditor_id' => ['present', 'nullable', 'integer', new ExistsForCompany('debt_creditors', $this->company_id)],
            'supplier_id' => ['present', 'nullable', 'integer', new ExistsForCompany('suppliers', $this->company_id)],
            'cash_account_id' => [
                'present',
                'nullable',
                'integer',
                'bail',
                new ExistsForCompany('cash_accounts', $this->company_id),
                new IsValidCashAccount($this->branch_id),
            ],
            'direct_amount_received' => ['required', 'numeric', 'min:0'],
            'opening_amount_due' => ['required', 'numeric', 'min:0'],
            'due_days' => ['required', 'integer', 'min:0'],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],

            'delete_payment_ids' => ['present', 'array'],
            'delete_payment_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('debt_payments', 'id')->where(function ($query) use ($debt) {
                    $query->where('company_id', $this->company_id)
                        ->where('debt_id', $debt->id);
                }),
            ],

            'payments' => ['present', 'array'],
            'payments.*.id' => [
                'present',
                'nullable',
                'integer',
                Rule::exists('debt_payments', 'id')->where(function ($query) use ($debt) {
                    $query->where('company_id', $this->company_id)
                        ->where('debt_id', $debt->id);
                }),
            ],
            'payments.*.code' => ['required', 'string', 'max:255'],
            'payments.*.date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'payments.*.cash_account_id' => [
                'required',
                'integer',
                'bail',
                new ExistsForCompany('cash_accounts', $this->company_id),
                new IsValidCashAccount($this->branch_id),
            ],
            'payments.*.amount' => ['required', 'numeric', 'gt:0'],
            'payments.*.remarks' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $directAmountReceived = (float) $this->input('direct_amount_received', 0);
            $openingAmountDue = (float) $this->input('opening_amount_due', 0);
            $amountTotal = $directAmountReceived + $openingAmountDue;
            $paymentTotal = collect($this->input('payments', []))
                ->sum(fn (array $payment) => (float) ($payment['amount'] ?? 0));
            $creditorId = $this->input('creditor_id');
            $supplierId = $this->input('supplier_id');
            $cashAccountId = $this->input('cash_account_id');

            if (filled($creditorId) && filled($supplierId)) {
                $validator->errors()->add('form', trans('rules.debt.party_must_be_single'));
            }

            if (! filled($creditorId) && ! filled($supplierId)) {
                $validator->errors()->add('form', trans('rules.debt.party_is_required'));
            }

            if ($amountTotal <= 0) {
                $validator->errors()->add('form', trans('rules.debt.amount_total_must_be_positive'));
            }

            if ($directAmountReceived > 0 && ! filled($cashAccountId)) {
                $validator->errors()->add('cash_account_id', trans('rules.debt.cash_account_is_required_for_direct_amount_received'));
            }

            if ($directAmountReceived <= 0 && filled($cashAccountId)) {
                $validator->errors()->add('direct_amount_received', trans('rules.debt.direct_amount_received_is_required_for_cash_account'));
            }

            if ($paymentTotal > $amountTotal) {
                $validator->errors()->add('payments', trans('rules.debt.payments_exceed_total_debt'));
            }
        });
    }

    public function attributes()
    {
        return array_merge(
            trans('validation_attributes.debt'),
            [
                'payments.*.id' => trans('validation_attributes.debt_payment.id'),
                'payments.*.code' => trans('validation_attributes.debt_payment.code'),
                'payments.*.date' => trans('validation_attributes.debt_payment.date'),
                'payments.*.cash_account_id' => trans('validation_attributes.debt_payment.cash_account_id'),
                'payments.*.amount' => trans('validation_attributes.debt_payment.amount'),
                'payments.*.remarks' => trans('validation_attributes.debt_payment.remarks'),
            ],
        );
    }

    public function messages()
    {
        return [
            'delete_payment_ids.*.exists' => trans('rules.debt.invalid_payment_reference'),
            'payments.*.id.exists' => trans('rules.debt.invalid_payment_reference'),
        ];
    }
}
