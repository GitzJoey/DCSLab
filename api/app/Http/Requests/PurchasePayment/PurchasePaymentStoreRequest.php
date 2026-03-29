<?php

namespace App\Http\Requests\PurchasePayment;

use App\Helpers\HashidsHelper;
use App\Models\PurchasePayment;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidCompany;
use App\Rules\IsValidPurchase;
use App\Rules\PurchasePaymentStoreValidCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchasePaymentStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', PurchasePayment::class) ? true : false;
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'purchase_id' => ['required', 'integer', new IsValidPurchase($this->company_id)],
            'code' => ['required', 'string', 'max:255', new PurchasePaymentStoreValidCode($this->company_id)],
            'date' => ['required', 'date'],
            'cash_account_id' => ['required', 'integer', new IsValidCashAccount($this->company_id)],
            'amount' => ['required', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.purchase_payment.company'),
            'branch_id' => trans('validation_attributes.purchase_payment.branch'),
            'purchase_id' => trans('validation_attributes.purchase_payment.purchase'),
            'code' => trans('validation_attributes.purchase_payment.code'),
            'date' => trans('validation_attributes.purchase_payment.date'),
            'cash_account_id' => trans('validation_attributes.purchase_payment.cash_account'),
            'amount' => trans('validation_attributes.purchase_payment.amount'),
            'remarks' => trans('validation_attributes.purchase_payment.remarks'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'purchase_id' => $this->filled('purchase_id') ? HashidsHelper::decodeId($this->purchase_id) : null,
            'cash_account_id' => $this->filled('cash_account_id') ? HashidsHelper::decodeId($this->cash_account_id) : null,
            'remarks' => $this->filled('remarks') ? $this->remarks : null,
        ]);
    }
}
