<?php

namespace App\Http\Requests\PurchaseAdditionalCostPayment;

use App\Helpers\HashidsHelper;
use App\Models\PurchaseAdditionalCost;
use App\Models\PurchaseAdditionalCostPayment;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidCompany;
use App\Rules\IsValidDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseAdditionalCostPaymentStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) return false;

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', PurchaseAdditionalCostPayment::class);
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'purchase_additional_cost_id' => $this->filled('purchase_additional_cost_id') ? HashidsHelper::decodeId($this->purchase_additional_cost_id) : null,
            'cash_account_id' => $this->filled('cash_account_id') ? HashidsHelper::decodeId($this->cash_account_id) : null,
        ]);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', 'bail', new IsValidBranch($this->company_id, true)],
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
            $purchaseAdditionalCost = PurchaseAdditionalCost::find($this->input('purchase_additional_cost_id'));
            if (! $purchaseAdditionalCost) {
                return;
            }

            if ((float) $this->input('amount', 0) > (float) $purchaseAdditionalCost->amount_payable_due) {
                $validator->errors()->add('amount', trans('rules.max.numeric', [
                    'attribute' => trans('validation_attributes.purchase_additional_cost_payment.amount'),
                    'max' => $purchaseAdditionalCost->amount_payable_due,
                ]));
            }
        });
    }

    public function attributes()
    {
        return trans('validation_attributes.purchase_additional_cost_payment');
    }
}
