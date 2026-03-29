<?php

namespace App\Http\Requests\SaleReceiptProductUnit;

use App\Helpers\HashidsHelper;
use App\Models\SaleReceiptProductUnit;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidProduct;
use App\Rules\IsValidProductUnit;
use App\Rules\IsValidSaleReceipt;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SaleReceiptProductUnitStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', SaleReceiptProductUnit::class);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'sale_receipt_id' => ['required', 'integer', new IsValidSaleReceipt($this->company_id)],
            'qty' => ['required', 'integer', 'min:1'],
            'product_id' => ['required', 'integer', new IsValidProduct($this->company_id)],
            'product_unit_id' => ['required', 'integer', new IsValidProductUnit()],
            'product_unit_amount_per_unit' => ['required', 'numeric', 'min:0'],
            'product_unit_amount_total' => ['required', 'numeric', 'min:0'],
            'is_has_sale' => ['required', 'boolean'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.sale_receipt_product_unit.company'),
            'branch_id' => trans('validation_attributes.sale_receipt_product_unit.branch'),
            'sale_receipt_id' => trans('validation_attributes.sale_receipt_product_unit.sale_receipt'),
            'qty' => trans('validation_attributes.sale_receipt_product_unit.qty'),
            'product_id' => trans('validation_attributes.sale_receipt_product_unit.product'),
            'product_unit_id' => trans('validation_attributes.sale_receipt_product_unit.product_unit'),
            'product_unit_amount_per_unit' => trans('validation_attributes.sale_receipt_product_unit.product_unit_amount_per_unit'),
            'product_unit_amount_total' => trans('validation_attributes.sale_receipt_product_unit.product_unit_total_amount'),
            'is_has_sale' => trans('validation_attributes.sale_receipt_product_unit.it_has_sale'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'is_has_sale' => $this->has('is_has_sale') ? $this->boolean('is_has_sale') : ($this->has('it_has_sale') ? $this->boolean('it_has_sale') : null),
            'product_unit_amount_total' => $this->filled('product_unit_amount_total') ? $this->product_unit_amount_total : ($this->filled('product_unit_total_amount') ? $this->product_unit_total_amount : null),
        ]);
    }
}
