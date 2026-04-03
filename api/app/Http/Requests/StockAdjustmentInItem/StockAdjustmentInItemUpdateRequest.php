<?php

namespace App\Http\Requests\StockAdjustmentInItem;

use App\Helpers\HashidsHelper;
use App\Models\ProductUnit;
use App\Models\StockAdjustmentInItem;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentInItemUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $resource = $this->route('stock_adjustment_in_item');

        return $user->can('update', StockAdjustmentInItem::class, $resource) ? true : false;
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'stock_adjustment_id' => ['required', 'integer', new ExistsForCompany('stock_adjustments', $this->company_id)],
            'qty' => ['required', 'numeric', 'min:1'],
            'product_unit_id' => ['required', 'integer', new ExistsForCompany('product_units', $this->company_id)],
            'product_unit_conversion_value' => ['required', 'numeric', 'min:1'],
            'product_unit_cogs' => ['required', 'numeric', 'min:0'],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],

            'delete_serial_ids' => ['present', 'array'],
            'delete_serial_ids.*' => ['required', 'integer', 'distinct', new ExistsForCompany('stock_adjustment_in_item_serials', $this->company_id)],
            'serials' => ['present', 'array'],
            'serials.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('stock_adjustment_in_item_serials', $this->company_id)],
            'serials.*.serial' => ['required', 'distinct', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.stock_adjustment_in_item.company_id'),
            'branch_id' => trans('validation_attributes.stock_adjustment_in_item.branch_id'),
            'stock_adjustment_id' => trans('validation_attributes.stock_adjustment_in_item.stock_adjustment_id'),
            'qty' => trans('validation_attributes.stock_adjustment_in_item.qty'),
            'product_unit_id' => trans('validation_attributes.stock_adjustment_in_item.product_unit_id'),
            'product_unit_conversion_value' => trans('validation_attributes.stock_adjustment_in_item.product_unit_conversion_value'),
            'product_unit_cogs' => trans('validation_attributes.stock_adjustment_in_item.product_unit_cogs'),
            'remarks' => trans('validation_attributes.stock_adjustment_in_item.remarks'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'stock_adjustment_id' => $this->filled('stock_adjustment_id') ? HashidsHelper::decodeId($this->stock_adjustment_id) : null,
            'product_unit_id' => $this->filled('product_unit_id') ? HashidsHelper::decodeId($this->product_unit_id) : null,
        ]);

        if ($this->filled('delete_serial_ids')) {
            $deleteSerialIds = $this->delete_serial_ids;
            foreach ($deleteSerialIds as $index => $id) {
                $deleteSerialIds[$index] = HashidsHelper::decodeId($id);
            }
            $this->merge(['delete_serial_ids' => $deleteSerialIds]);
        }

        if (is_array($this->input('serials'))) {
            $serials = [];
            foreach ($this->input('serials') as $item) {
                if (isset($item['id'])) {
                    $item['id'] = HashidsHelper::decodeId($item['id']);
                }
                $serials[] = $item;
            }
            $this->merge(['serials' => $serials]);
        }
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
                $validator->errors()->add('serials', trans('validation.stock_adjustment_in_item.base_qty_must_be_integer'));

                return;
            }

            $serialCount = (string) count(is_array($serials) ? $serials : []);
            if (bccomp($serialCount, $baseQty, 8) !== 0) {
                $validator->errors()->add('serials', trans('validation.stock_adjustment_in_item.serial_count_not_match_qty'));
            }
        });
    }
}
