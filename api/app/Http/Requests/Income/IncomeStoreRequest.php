<?php

namespace App\Http\Requests\Income;

use App\Helpers\HashidsHelper;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidCompany;
use App\Rules\IsValidDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class IncomeStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', Income::class);
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'income_category_id' => $this->filled('income_category_id') ? HashidsHelper::decodeId($this->income_category_id) : null,
            'paid_immediately_cash_account_id' => $this->filled('paid_immediately_cash_account_id') ? HashidsHelper::decodeId($this->paid_immediately_cash_account_id) : null,
        ]);

        if (is_array($this->input('payments'))) {
            $payments = [];
            foreach ($this->input('payments') as $payment) {
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
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', 'bail', new IsValidBranch($this->company_id, true)],
            'code' => ['required', 'string', 'max:255'],
            'date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'income_category_id' => ['required', 'integer', new ExistsForCompany('income_categories', $this->company_id)],
            'paid_immediately_cash_account_id' => [
                'present',
                'nullable',
                'integer',
                'bail',
                new ExistsForCompany('cash_accounts', $this->company_id),
                new IsValidCashAccount($this->branch_id),
            ],
            'amount_paid_immediately' => ['required', 'numeric', 'min:0'],
            'amount_receivable' => ['required', 'numeric', 'min:0'],
            'due_days' => ['required', 'integer', 'min:0'],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],

            'image_hashes' => ['present', 'array'],
            'image_hashes.*.hash' => ['required', 'string', Rule::exists('income_images', 'hash')],
            'image_hashes.*.is_main' => ['required', 'boolean'],

            'payments' => ['present', 'array'],
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
            $amountPaidImmediately = (float) $this->input('amount_paid_immediately', 0);
            $amountReceivable = (float) $this->input('amount_receivable', 0);
            $paymentTotal = collect($this->input('payments', []))
                ->sum(fn (array $payment) => (float) ($payment['amount'] ?? 0));

            if ($amountPaidImmediately > 0 && empty($this->input('paid_immediately_cash_account_id'))) {
                $validator->errors()->add('paid_immediately_cash_account_id', trans('validation.required', [
                    'attribute' => trans('validation_attributes.income.paid_immediately_cash_account_id'),
                ]));
            }

            if ($amountPaidImmediately <= 0 && $amountReceivable <= 0) {
                $validator->errors()->add('amount_total', trans('rules.income.amount_total_must_be_positive'));
            }

            if ($paymentTotal > $amountReceivable) {
                $validator->errors()->add('payments', trans('rules.income.payments_exceed_amount_receivable'));
            }

            if (! $validator->errors()->has('income_category_id')) {
                $incomeCategory = IncomeCategory::query()
                    ->select('id')
                    ->find($this->input('income_category_id'));

                if ($incomeCategory && $incomeCategory->children()->exists()) {
                    $validator->errors()->add('income_category_id', trans('rules.income_category.must_not_have_children'));
                }
            }
        });
    }

    public function attributes()
    {
        return array_merge(
            trans('validation_attributes.income'),
            [
                'payments.*.code' => trans('validation_attributes.income_payment.code'),
                'payments.*.date' => trans('validation_attributes.income_payment.date'),
                'payments.*.cash_account_id' => trans('validation_attributes.income_payment.cash_account_id'),
                'payments.*.amount' => trans('validation_attributes.income_payment.amount'),
                'payments.*.remarks' => trans('validation_attributes.income_payment.remarks'),
            ],
        );
    }
}
