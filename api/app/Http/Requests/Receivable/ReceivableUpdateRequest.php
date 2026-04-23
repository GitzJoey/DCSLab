<?php

namespace App\Http\Requests\Receivable;

use App\Helpers\HashidsHelper;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ReceivableUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $receivable = $this->route('receivable');

        return $user->can('update', $receivable);
    }

    public function prepareForValidation()
    {
        $receivable = $this->route('receivable');
        $deletePaymentIds = collect($this->input('delete_payment_ids', []))
            ->map(fn ($id) => HashidsHelper::decodeId($id))
            ->all();

        $this->merge([
            'company_id' => $receivable->company_id,
            'branch_id' => $receivable->branch_id,
            'category_id' => $this->filled('category_id') ? HashidsHelper::decodeId($this->category_id) : null,
            'customer_id' => $this->filled('customer_id') ? HashidsHelper::decodeId($this->customer_id) : null,
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
        $receivable = $this->route('receivable');

        return [
            'company_id' => ['required', 'integer'],
            'branch_id' => ['required', 'integer'],
            'code' => ['required', 'string', 'max:255'],
            'date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'category_id' => ['required', 'integer', new ExistsForCompany('receivable_categories', $this->company_id)],
            'customer_id' => ['required', 'integer', new ExistsForCompany('customers', $this->company_id)],
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
                Rule::exists('receivable_payments', 'id')->where(function ($query) use ($receivable) {
                    $query->where('company_id', $this->company_id)
                        ->where('receivable_id', $receivable->id);
                }),
            ],

            'payments' => ['present', 'array'],
            'payments.*.id' => [
                'present',
                'nullable',
                'integer',
                Rule::exists('receivable_payments', 'id')->where(function ($query) use ($receivable) {
                    $query->where('company_id', $this->company_id)
                        ->where('receivable_id', $receivable->id);
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
            $customerId = $this->input('customer_id');
            $cashAccountId = $this->input('cash_account_id');

            if (! filled($customerId)) {
                $validator->errors()->add('customer_id', trans('validation.required', [
                    'attribute' => trans('validation_attributes.receivable.customer_id'),
                ]));
            }

            if ($amountTotal <= 0) {
                $validator->errors()->add('form', trans('rules.receivable.amount_total_must_be_positive'));
            }

            if ($directAmountReceived > 0 && ! filled($cashAccountId)) {
                $validator->errors()->add('cash_account_id', trans('rules.receivable.cash_account_is_required_for_direct_amount_received'));
            }

            if ($directAmountReceived <= 0 && filled($cashAccountId)) {
                $validator->errors()->add('direct_amount_received', trans('rules.receivable.direct_amount_received_is_required_for_cash_account'));
            }

            if ($paymentTotal > $amountTotal) {
                $validator->errors()->add('payments', trans('rules.receivable.payments_exceed_total_receivable'));
            }
        });
    }

    public function attributes()
    {
        return array_merge(
            trans('validation_attributes.receivable'),
            [
                'payments.*.id' => trans('validation_attributes.receivable_payment.id'),
                'payments.*.code' => trans('validation_attributes.receivable_payment.code'),
                'payments.*.date' => trans('validation_attributes.receivable_payment.date'),
                'payments.*.cash_account_id' => trans('validation_attributes.receivable_payment.cash_account_id'),
                'payments.*.amount' => trans('validation_attributes.receivable_payment.amount'),
                'payments.*.remarks' => trans('validation_attributes.receivable_payment.remarks'),
            ],
        );
    }

    public function messages()
    {
        return [
            'delete_payment_ids.*.exists' => trans('rules.receivable.invalid_payment_reference'),
            'payments.*.id.exists' => trans('rules.receivable.invalid_payment_reference'),
        ];
    }
}
