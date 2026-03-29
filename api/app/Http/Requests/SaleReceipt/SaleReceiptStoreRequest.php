<?php

namespace App\Http\Requests\SaleReceipt;

use App\Helpers\HashidsHelper;
use App\Models\SaleReceipt;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidSale;
use App\Rules\IsValidWarehouse;
use App\Rules\SaleReceiptStoreValidCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SaleReceiptStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', SaleReceipt::class);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'code' => ['required', 'string', 'max:255', new SaleReceiptStoreValidCode($this->company_id)],
            'sale_id' => ['required', 'integer', new IsValidSale()],
            'warehouse_id' => ['required', 'integer', new IsValidWarehouse($this->company_id, true)],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.sale_receipt.company'),
            'code' => trans('validation_attributes.sale_receipt.code'),
            'remarks' => trans('validation_attributes.sale_receipt.remarks'),
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
