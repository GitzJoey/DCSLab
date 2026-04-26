<?php

namespace App\Http\Requests\StockTransferItem;

use App\Helpers\HashidsHelper;
use App\Models\ProductUnit;
use App\Models\StockTransferItem;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidProductUnit;
use App\Rules\IsValidStockTransfer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StockTransferItemStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', StockTransferItem::class) ? true : false;
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'stock_transfer_id' => ['required', 'integer', 'bail', new IsValidStockTransfer()],
            'qty' => ['required', 'numeric', 'min:1'],
            'product_unit_id' => ['required', 'integer', 'bail', new IsValidProductUnit($this->company_id)],
            'product_unit_conversion_value' => ['required', 'numeric', 'min:1'],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],

            'serials' => ['present', 'array'],
            'serials.*.serial' => ['required', 'distinct', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.stock_transfer_item.company'),
            'branch_id' => trans('validation_attributes.stock_transfer_item.branch'),
            'stock_transfer_id' => trans('validation_attributes.stock_transfer_item.stock_transfer'),
            'qty' => trans('validation_attributes.stock_transfer_item.qty'),
            'product_unit_id' => trans('validation_attributes.stock_transfer_item.product_unit'),
            'product_unit_conversion_value' => trans('validation_attributes.stock_adjustment_in_item.product_unit_conversion_value'),
            'remarks' => trans('validation_attributes.stock_transfer_item.remarks'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'stock_transfer_id' => $this->filled('stock_transfer_id') ? HashidsHelper::decodeId($this->stock_transfer_id) : null,
            'product_unit_id' => $this->filled('product_unit_id') ? HashidsHelper::decodeId($this->product_unit_id) : null,
        ]);

    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $productUnitId = $validator->getData()['product_unit_id'] ?? null;
            $qty = $validator->getData()['qty'] ?? null;
            $conversionValue = $validator->getData()['product_unit_conversion_value'] ?? null;
            $serials = $validator->getData()['serials'] ?? [];

            if (empty($productUnitId) || ! is_numeric($qty) || ! is_numeric($conversionValue)) {
                return;
            }

            $product = ProductUnit::with('product')->find($productUnitId)?->product;
            if (! $product?->is_use_serial_number) {
                return;
            }

            $baseQty = bcmul((string) $qty, (string) $conversionValue, 8);
            $normalizedBaseQty = rtrim(rtrim($baseQty, '0'), '.');
            if (str_contains($normalizedBaseQty, '.')) {
                $validator->errors()->add('serials', trans('rules.stock_transfer.serial_base_qty_must_be_integer'));

                return;
            }

            $serialCount = (string) count(is_array($serials) ? $serials : []);
            if (bccomp($serialCount, $baseQty, 8) !== 0) {
                $validator->errors()->add('serials', trans('rules.stock_transfer.serial_count_must_match_base_qty'));
            }
        });
    }
}
