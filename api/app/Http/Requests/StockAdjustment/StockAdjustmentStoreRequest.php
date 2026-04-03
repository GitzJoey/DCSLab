<?php

namespace App\Http\Requests\StockAdjustment;

use App\Helpers\HashidsHelper;
use App\Models\ProductUnit;
use App\Models\StockAdjustment;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidDate;
use App\Rules\IsValidWarehouse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', StockAdjustment::class) ? true : false;
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'code' => ['required', 'string', 'max:255'],
            'date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'category_id' => ['required', 'integer', new ExistsForCompany('stock_adjustment_categories', $this->company_id)],
            'in_warehouse_id' => ['present', 'nullable', 'integer', 'required_without:out_warehouse_id', new IsValidWarehouse($this->company_id, false)],
            'out_warehouse_id' => ['present', 'nullable', 'integer', 'different:in_warehouse_id', 'required_without:in_warehouse_id', new IsValidWarehouse($this->company_id, false)],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],
            'is_posted' => ['required', 'boolean'],

            'in_items' => ['array', 'required_with:in_warehouse_id'],
            'in_items.*.qty' => ['required', 'numeric', 'min:1'],
            'in_items.*.product_unit_id' => ['required', 'integer', 'distinct', new ExistsForCompany('product_units', $this->company_id)],
            'in_items.*.product_unit_conversion_value' => ['required', 'numeric', 'min:1'],
            'in_items.*.product_unit_cogs' => ['required', 'numeric', 'min:0'],
            'in_items.*.remarks' => ['present', 'nullable', 'string', 'max:255'],

            'in_items.*.serials' => ['present', 'array'],
            'in_items.*.serials.*.serial' => ['required', 'distinct', 'string', 'max:255'],

            'out_items' => ['array', 'required_with:out_warehouse_id'],
            'out_items.*.qty' => ['required', 'numeric', 'min:1'],
            'out_items.*.product_unit_id' => ['required', 'integer', 'distinct', new ExistsForCompany('product_units', $this->company_id)],
            'out_items.*.product_unit_conversion_value' => ['required', 'numeric', 'min:1'],
            'out_items.*.remarks' => ['present', 'nullable', 'string', 'max:255'],

            'out_items.*.serials' => ['present', 'array'],
            'out_items.*.serials.*.serial' => ['required', 'distinct', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.stock_adjustment.company_id'),
            'branch_id' => trans('validation_attributes.stock_adjustment.branch_id'),
            'code' => trans('validation_attributes.stock_adjustment.code'),
            'date' => trans('validation_attributes.stock_adjustment.date'),
            'category_id' => trans('validation_attributes.stock_adjustment.category_id'),
            'in_warehouse_id' => trans('validation_attributes.stock_adjustment.in_warehouse_id'),
            'out_warehouse_id' => trans('validation_attributes.stock_adjustment.out_warehouse_id'),
            'remarks' => trans('validation_attributes.stock_adjustment.remarks'),
            'is_posted' => trans('validation_attributes.stock_adjustment.is_posted'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'in_warehouse_id' => $this->filled('in_warehouse_id') ? HashidsHelper::decodeId($this->in_warehouse_id) : null,
            'out_warehouse_id' => $this->filled('out_warehouse_id') ? HashidsHelper::decodeId($this->out_warehouse_id) : null,
            'category_id' => $this->filled('category_id') ? HashidsHelper::decodeId($this->category_id) : null,
        ]);

        if (is_array($this->input('in_items'))) {
            $inItems = [];
            foreach ($this->input('in_items') as $item) {
                if (isset($item['product_unit_id'])) {
                    $item['product_unit_id'] = HashidsHelper::decodeId($item['product_unit_id']);
                }
                $inItems[] = $item;
            }
            $this->merge(['in_items' => $inItems]);
        }

        if (is_array($this->input('out_items'))) {
            $outItems = [];
            foreach ($this->input('out_items') as $item) {
                if (isset($item['product_unit_id'])) {
                    $item['product_unit_id'] = HashidsHelper::decodeId($item['product_unit_id']);
                }
                $outItems[] = $item;
            }
            $this->merge(['out_items' => $outItems]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // in_items
            $inItems = $validator->getData()['in_items'] ?? [];
            if ($validator->errors()->isNotEmpty() || ! is_array($inItems) || empty($inItems)) {
                return;
            }

            $inItemUnits = ProductUnit::with('product')
                ->whereIn('id', collect($inItems)->pluck('product_unit_id')->filter()->unique()->values()->all())
                ->get()
                ->keyBy('id');

            foreach ($inItems as $i => $item) {
                if (! is_array($item)) {
                    continue;
                }

                $productUnitId = $item['product_unit_id'] ?? null;
                if (empty($productUnitId)) {
                    continue;
                }

                $product = $inItemUnits->get((int) $productUnitId)?->product;
                if ($product?->is_use_serial_number) {
                    $qty = $item['qty'] ?? null;
                    $conversionValue = $item['product_unit_conversion_value'] ?? null;
                    if (! is_numeric($qty) || ! is_numeric($conversionValue)) {
                        continue;
                    }

                    $baseQty = bcmul((string) $qty, (string) $conversionValue, 8);
                    $normalizedBaseQty = rtrim(rtrim($baseQty, '0'), '.');
                    if (str_contains($normalizedBaseQty, '.')) {
                        $validator->errors()->add('in_items.'.$i.'.serials', trans('validation.stock_adjustment_in_item.base_qty_must_be_integer'));

                        continue;
                    }

                    $serialCount = (string) count($item['serials'] ?? []);
                    if (bccomp($serialCount, $baseQty, 8) !== 0) {
                        $validator->errors()->add('in_items.'.$i.'.serials', trans('validation.stock_adjustment_in_item.serial_count_not_match_qty'));
                    }
                }
            }

            // out_items
            $outItems = $validator->getData()['out_items'] ?? [];
            if (! is_array($outItems) || empty($outItems)) {
                return;
            }

            $outItemUnits = ProductUnit::with('product')
                ->whereIn('id', collect($outItems)->pluck('product_unit_id')->filter()->unique()->values()->all())
                ->get()
                ->keyBy('id');

            foreach ($outItems as $i => $item) {
                if (! is_array($item)) {
                    continue;
                }

                $productUnitId = $item['product_unit_id'] ?? null;
                if (empty($productUnitId)) {
                    continue;
                }

                $product = $outItemUnits->get((int) $productUnitId)?->product;
                if ($product?->is_use_serial_number) {
                    $qty = $item['qty'] ?? null;
                    $conversionValue = $item['product_unit_conversion_value'] ?? null;
                    if (! is_numeric($qty) || ! is_numeric($conversionValue)) {
                        continue;
                    }

                    $baseQty = bcmul((string) $qty, (string) $conversionValue, 8);
                    $normalizedBaseQty = rtrim(rtrim($baseQty, '0'), '.');
                    if (str_contains($normalizedBaseQty, '.')) {
                        $validator->errors()->add('out_items.'.$i.'.serials', trans('validation.stock_adjustment_out_item.base_qty_must_be_integer'));

                        continue;
                    }

                    $serialCount = (string) count($item['serials'] ?? []);
                    if (bccomp($serialCount, $baseQty, 8) !== 0) {
                        $validator->errors()->add('out_items.'.$i.'.serials', trans('validation.stock_adjustment_out_item.serial_count_not_match_qty'));
                    }
                }
            }
        });
    }
}
