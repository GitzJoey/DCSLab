<?php

namespace App\Http\Requests\PurchaseReceiptProductUnit;

use App\Helpers\HashidsHelper;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidProduct;
use App\Rules\IsValidProductUnit;
use App\Rules\IsValidPurchase;
use App\Rules\IsValidPurchaseReceipt;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseReceiptProductUnitUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $purchaseReceiptProductUnit = $this->route('purchase_receipt_product_unit');

        return $user->can('update', $purchaseReceiptProductUnit);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'purchase_receipt_id' => ['required', 'integer', new IsValidPurchaseReceipt($this->company_id)],
            'purchase_id' => ['required', 'integer', new IsValidPurchase()],
            'qty' => ['required', 'numeric', 'min:0'],
            'product_id' => ['required', 'integer', new IsValidProduct($this->company_id)],
            'product_unit_id' => ['required', 'integer', new IsValidProductUnit()],
            'product_unit_amount_per_unit' => ['required', 'numeric', 'min:0'],
            'product_unit_amount_total' => ['required', 'numeric', 'min:0'],
            'is_has_purchase' => ['required', 'boolean'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.purchase_receipt_product_unit.company'),
            'branch_id' => trans('validation_attributes.purchase_receipt_product_unit.branch'),
            'purchase_receipt_id' => trans('validation_attributes.purchase_receipt_product_unit.purchase_receipt'),
            'purchase_id' => trans('validation_attributes.purchase_receipt_product_unit.purchase'),
            'qty' => trans('validation_attributes.purchase_receipt_product_unit.qty'),
            'product_id' => trans('validation_attributes.purchase_receipt_product_unit.product'),
            'product_unit_id' => trans('validation_attributes.purchase_receipt_product_unit.product_unit'),
            'product_unit_amount_per_unit' => trans('validation_attributes.purchase_receipt_product_unit.product_unit_amount_per_unit'),
            'product_unit_amount_total' => trans('validation_attributes.purchase_receipt_product_unit.product_unit_amount_total'),
            'is_has_purchase' => trans('validation_attributes.purchase_receipt_product_unit.is_has_purchase'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
        ]);
    }
}
