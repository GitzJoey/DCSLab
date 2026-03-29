<?php

namespace App\Http\Requests\SaleOrderDownPaymentApply;

use App\Helpers\HashidsHelper;
use App\Models\SaleOrderDownPaymentApply;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidCompany;
use App\Rules\IsValidSaleOrder;
use App\Rules\SaleOrderDownPaymentApplyStoreValidCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SaleOrderDownPaymentApplyStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', SaleOrderDownPaymentApply::class);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'sale_order_id' => ['required', 'integer', 'bail', new IsValidSaleOrder()],
            'code' => ['required', 'string', 'max:255', new SaleOrderDownPaymentApplyStoreValidCode($this->company_id)],
            'date' => ['required', 'date'],
            'cash_account_id' => ['required', 'integer', new IsValidCashAccount($this->branch_id)],
            'amount' => ['required', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.sale_order_down_payment_apply.company'),
            'branch_id' => trans('validation_attributes.sale_order_down_payment_apply.branch'),
            'sale_order_id' => trans('validation_attributes.sale_order_down_payment_apply.sales_order'),
            'code' => trans('validation_attributes.sale_order_down_payment_apply.code'),
            'date' => trans('validation_attributes.sale_order_down_payment_apply.date'),
            'cash_account_id' => trans('validation_attributes.sale_order_down_payment_apply.cash_account'),
            'amount' => trans('validation_attributes.sale_order_down_payment_apply.amount'),
            'remarks' => trans('validation_attributes.sale_order_down_payment_apply.remarks'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'remarks' => $this->filled('remarks') ? $this['remarks'] : null,
        ]);
    }
}
