<?php

namespace App\Http\Requests\SaleOrderDownPayment;

use App\Helpers\HashidsHelper;
use App\Models\SaleOrderDownPayment;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidCompany;
use App\Rules\IsValidSaleOrder;
use App\Rules\SaleOrderDownPaymentStoreValidCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SaleOrderDownPaymentStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', SaleOrderDownPayment::class);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'sale_order_id' => ['required', 'integer', 'bail', new IsValidSaleOrder()],
            'code' => ['required', 'string', 'max:255', new SaleOrderDownPaymentStoreValidCode($this->company_id)],
            'date' => ['required', 'date'],
            'cash_account_id' => ['required', 'integer', 'bail', new IsValidCashAccount($this->branch_id)],
            'amount' => ['required', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.sale_order_down_payment.company'),
            'code' => trans('validation_attributes.sale_order_down_payment.code'),
            'remarks' => trans('validation_attributes.sale_order_down_payment.remarks'),
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
