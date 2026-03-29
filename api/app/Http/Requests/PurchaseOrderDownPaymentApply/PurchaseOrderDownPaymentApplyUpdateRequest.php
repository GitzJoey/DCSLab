<?php

namespace App\Http\Requests\PurchaseOrderDownPaymentApply;

use App\Helpers\HashidsHelper;
use App\Models\PurchaseOrderDownPaymentApply;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidCompany;
use App\Rules\IsValidPurchaseOrder;
use App\Rules\PurchaseOrderDownPaymentApplyUpdateValidCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderDownPaymentApplyUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $purchaseOrderDownPaymentApply = $this->route('purchase_order_down_payment_apply');

        return $user->can('update', PurchaseOrderDownPaymentApply::class, $purchaseOrderDownPaymentApply) ? true : false;
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'purchase_order_id' => ['required', 'integer', 'bail', new IsValidPurchaseOrder()],
            'code' => ['required', 'string', 'max:255', new PurchaseOrderDownPaymentApplyUpdateValidCode($this->company_id, $this->route('purchase_order_down_payment_apply'))],
            'date' => ['required', 'date'],
            'cash_account_id' => ['required', 'integer', 'bail', new IsValidCashAccount($this->company_id)],
            'amount' => ['required', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.purchase_order_down_payment_apply.company_id'),
            'branch_id' => trans('validation_attributes.purchase_order_down_payment_apply.branch_id'),
            'purchase_order_id' => trans('validation_attributes.purchase_order_down_payment_apply.purchase_order_id'),
            'code' => trans('validation_attributes.purchase_order_down_payment_apply.code'),
            'date' => trans('validation_attributes.purchase_order_down_payment_apply.date'),
            'cash_account_id' => trans('validation_attributes.purchase_order_down_payment_apply.cash_account_id'),
            'amount' => trans('validation_attributes.purchase_order_down_payment_apply.amount'),
            'remarks' => trans('validation_attributes.purchase_order_down_payment_apply.remarks'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'purchase_order_id' => $this->filled('purchase_order_id') ? HashidsHelper::decodeId($this->purchase_order_id) : null,
            'cash_account_id' => $this->filled('cash_account_id') ? HashidsHelper::decodeId($this->cash_account_id) : null,
            'remarks' => $this->filled('remarks') ? $this->remarks : null,
        ]);
    }
}
