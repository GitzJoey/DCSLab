<?php

namespace App\Http\Requests\PurchaseAdditionalCost;

use App\Helpers\HashidsHelper;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseAdditionalCostUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) return false;

        /** @var \App\User */
        $user = Auth::user();
        $purchaseAdditionalCost = $this->route('purchase_additional_cost');

        return $user->can('update', $purchaseAdditionalCost);
    }

    public function prepareForValidation()
    {
        $purchaseAdditionalCost = $this->route('purchase_additional_cost');

        $this->merge([
            'company_id' => $purchaseAdditionalCost->company_id,
            'branch_id' => $purchaseAdditionalCost->branch_id,
            'purchase_id' => $this->filled('purchase_id') ? HashidsHelper::decodeId($this->purchase_id) : null,
            'purchase_additional_cost_category_id' => $this->filled('purchase_additional_cost_category_id') ? HashidsHelper::decodeId($this->purchase_additional_cost_category_id) : null,
            'paid_immediately_cash_account_id' => $this->filled('paid_immediately_cash_account_id') ? HashidsHelper::decodeId($this->paid_immediately_cash_account_id) : null,
        ]);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer'],
            'branch_id' => ['required', 'integer'],
            'purchase_id' => ['required', 'integer', new ExistsForCompany('purchases', $this->company_id)],
            'code' => ['required', 'string'],
            'date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'due_days' => ['required', 'integer', 'min:0'],
            'purchase_additional_cost_category_id' => ['required', 'integer', new ExistsForCompany('purchase_additional_cost_categories', $this->company_id)],
            'paid_immediately_cash_account_id' => [
                'present',
                'nullable',
                'integer',
                'bail',
                new ExistsForCompany('cash_accounts', $this->company_id),
                new IsValidCashAccount($this->branch_id),
            ],
            'amount_paid_immediately' => ['required', 'numeric', 'min:0'],
            'amount_payable' => ['required', 'numeric', 'min:0'],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $amountPaidImmediately = (float) $this->input('amount_paid_immediately', 0);
            $amountPayable = (float) $this->input('amount_payable', 0);

            if ($amountPaidImmediately > 0 && empty($this->input('paid_immediately_cash_account_id'))) {
                $validator->errors()->add('paid_immediately_cash_account_id', trans('validation.required', [
                    'attribute' => trans('validation_attributes.purchase_additional_cost.paid_immediately_cash_account_id'),
                ]));
            }

            if ($amountPaidImmediately <= 0 && $amountPayable <= 0) {
                $validator->errors()->add('amount_total', trans('rules.purchase_additional_cost.amount_total_must_be_positive'));
            }
        });
    }

    public function attributes()
    {
        return trans('validation_attributes.purchase_additional_cost');
    }
}
