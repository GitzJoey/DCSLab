<?php

namespace App\Http\Requests\PurchaseReceipt;

use App\Helpers\HashidsHelper;
use App\Models\PurchaseReceipt;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidPurchase;
use App\Rules\IsValidWarehouse;
use App\Rules\PurchaseReceiptUpdateValidCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseReceiptUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $purchaseReceipt = $this->route('purchase_receipt');

        return $user->can('update', PurchaseReceipt::class, $purchaseReceipt) ? true : false;
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'code' => ['required', 'string', 'max:255', new PurchaseReceiptUpdateValidCode($this->company_id, $this->route('purchase_receipt'))],
            'purchase_id' => ['required', 'integer', new IsValidPurchase($this->company_id)],
            'warehouse_id' => ['required', 'integer', new IsValidWarehouse($this->company_id, true)],
            'is_posted' => ['required', 'boolean'],
            'is_valid' => ['required', 'boolean'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.purchase_receipt.company'),
            'branch_id' => trans('validation_attributes.purchase_receipt.branch'),
            'code' => trans('validation_attributes.purchase_receipt.code'),
            'purchase_id' => trans('validation_attributes.purchase_receipt.purchase'),
            'warehouse_id' => trans('validation_attributes.purchase_receipt.warehouse'),
            'is_posted' => trans('validation_attributes.purchase_receipt.is_posted'),
            'is_valid' => trans('validation_attributes.purchase_receipt.is_valid'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'purchase_id' => $this->filled('purchase_id') ? HashidsHelper::decodeId($this->purchase_id) : null,
            'warehouse_id' => $this->filled('warehouse_id') ? HashidsHelper::decodeId($this->warehouse_id) : null,
        ]);
    }
}
