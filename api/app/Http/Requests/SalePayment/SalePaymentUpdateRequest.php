<?php

namespace App\Http\Requests\SalePayment;

use App\Helpers\HashidsHelper;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidCompany;
use App\Rules\IsValidSale;
use App\Rules\SalePaymentUpdateValidCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SalePaymentUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $salePayment = $this->route('sale_payment');

        return $user->can('update', $salePayment);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', 'bail', new IsValidBranch($this->company_id, true)],
            'sale_id' => ['required', 'integer', 'bail', new IsValidSale()],
            'code' => ['required', 'string', 'max:255', new SalePaymentUpdateValidCode($this->company_id, $this->route('sale_payment'))],
            'date' => ['required', 'date'],
            'cash_account_id' => ['required', 'integer', 'bail', new IsValidCashAccount($this->company_id)],
            'amount' => ['required', 'numeric', 'min:0'],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.sale_payment.company'),
            'branch_id' => trans('validation_attributes.sale_payment.branch'),
            'sale_id' => trans('validation_attributes.sale_payment.sale'),
            'code' => trans('validation_attributes.sale_payment.code'),
            'date' => trans('validation_attributes.sale_payment.date'),
            'cash_account_id' => trans('validation_attributes.sale_payment.cash_account'),
            'amount' => trans('validation_attributes.sale_payment.amount'),
            'remarks' => trans('validation_attributes.sale_payment.remarks'),
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
