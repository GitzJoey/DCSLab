<?php

namespace App\Http\Requests\PurchaseAdditionalCostPayment;

use App\Helpers\HashidsHelper;
use App\Models\PurchaseAdditionalCostPayment;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseAdditionalCostPaymentUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) return false;

        /** @var \App\User */
        $user = Auth::user();
        $purchaseAdditionalCostPayment = $this->route('purchase_additional_cost_payment');

        return $user->can('update', $purchaseAdditionalCostPayment);
    }

    public function prepareForValidation()
    {
        $purchaseAdditionalCostPayment = $this->route('purchase_additional_cost_payment');

        $this->merge([
            'company_id' => $purchaseAdditionalCostPayment->company_id,
            'branch_id' => $purchaseAdditionalCostPayment->branch_id,
            'purchase_additional_cost_id' => $purchaseAdditionalCostPayment->purchase_additional_cost_id,
            'cash_account_id' => $this->filled('cash_account_id') ? HashidsHelper::decodeId($this->cash_account_id) : null,
        ]);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer'],
            'branch_id' => ['required', 'integer'],
            'purchase_additional_cost_id' => ['required', 'integer', new ExistsForCompany('purchase_additional_costs', $this->company_id)],
            'code' => ['required', 'string'],
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
            /** @var PurchaseAdditionalCostPayment $payment */
            $payment = $this->route('purchase_additional_cost_payment');
            $purchaseAdditionalCost = $payment->purchaseAdditionalCost;

            if (! $purchaseAdditionalCost) {
                return;
            }

            $maxAmount = (float) $purchaseAdditionalCost->amount_payable_due + (float) $payment->amount;
            if ((float) $this->input('amount', 0) > $maxAmount) {
                $validator->errors()->add('amount', trans('rules.max.numeric', [
                    'attribute' => trans('validation_attributes.purchase_additional_cost_payment.amount'),
                    'max' => $maxAmount,
                ]));
            }
        });
    }

    public function attributes()
    {
        return trans('validation_attributes.purchase_additional_cost_payment');
    }
}
