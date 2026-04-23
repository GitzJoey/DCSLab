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

class StockAdjustmentUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $stockAdjustment = $this->route('stock_adjustment');

        return $user->can('update', StockAdjustment::class, $stockAdjustment) ? true : false;
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
            'out_warehouse_id' => ['present', 'nullable', 'integer', 'required_without:in_warehouse_id', new IsValidWarehouse($this->company_id, false)],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],
            'is_posted' => ['required', 'boolean'],

            'delete_in_item_ids' => ['present', 'array'],
            'delete_in_item_ids.*' => ['required', 'integer', 'distinct', new ExistsForCompany('stock_adjustment_in_items', $this->company_id)],
            'in_items' => ['array', 'required_with:in_warehouse_id'],
            'in_items.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('stock_adjustment_in_items', $this->company_id)],
            'in_items.*.qty' => ['required', 'numeric', 'min:1'],
            'in_items.*.product_unit_id' => ['required', 'integer', 'distinct', new ExistsForCompany('product_units', $this->company_id)],
            'in_items.*.product_unit_conversion_value' => ['required', 'numeric', 'min:1'],
            'in_items.*.product_unit_cogs' => ['required', 'numeric', 'min:0'],
            'in_items.*.remarks' => ['present', 'nullable', 'string', 'max:255'],

            'in_items.*.delete_serial_ids' => ['present', 'array'],
            'in_items.*.delete_serial_ids.*' => ['required', 'integer', 'distinct', new ExistsForCompany('stock_adjustment_in_item_serials', $this->company_id)],
            'in_items.*.serials' => ['present', 'array'],
            'in_items.*.serials.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('stock_adjustment_in_item_serials', $this->company_id)],
            'in_items.*.serials.*.serial' => ['required', 'distinct', 'string', 'max:255'],

            'delete_out_item_ids' => ['present', 'array'],
            'delete_out_item_ids.*' => ['required', 'integer', 'distinct', new ExistsForCompany('stock_adjustment_out_items', $this->company_id)],
            'out_items' => ['array', 'required_with:out_warehouse_id'],
            'out_items.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('stock_adjustment_out_items', $this->company_id)],
            'out_items.*.qty' => ['required', 'numeric', 'min:1'],
            'out_items.*.product_unit_id' => ['required', 'integer', 'distinct', new ExistsForCompany('product_units', $this->company_id)],
            'out_items.*.product_unit_conversion_value' => ['required', 'numeric', 'min:1'],
            'out_items.*.remarks' => ['present', 'nullable', 'string', 'max:255'],

            'out_items.*.delete_serial_ids' => ['present', 'array'],
            'out_items.*.delete_serial_ids.*' => ['required', 'integer', 'distinct', new ExistsForCompany('stock_adjustment_out_item_serials', $this->company_id)],
            'out_items.*.serials' => ['present', 'array'],
            'out_items.*.serials.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('stock_adjustment_out_item_serials', $this->company_id)],
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

        if (is_array($this->input('delete_in_item_ids'))) {
            $deleteInItemIds = [];
            foreach ($this->input('delete_in_item_ids') as $deleteInItemId) {
                $deleteInItemIds[] = HashidsHelper::decodeId($deleteInItemId);
            }
            $this->merge(['delete_in_item_ids' => $deleteInItemIds]);
        }

        if (is_array($this->input('in_items'))) {
            $inItems = [];
            foreach ($this->input('in_items') as $item) {
                if (! empty($item['id'])) {
                    $item['id'] = HashidsHelper::decodeId($item['id']);
                }
                if (isset($item['product_unit_id'])) {
                    $item['product_unit_id'] = HashidsHelper::decodeId($item['product_unit_id']);
                }

                if (array_key_exists('delete_serial_ids', $item) && is_array($item['delete_serial_ids'])) {
                    $deleteSerialIds = [];
                    foreach ($item['delete_serial_ids'] as $deleteSerialId) {
                        $deleteSerialIds[] = HashidsHelper::decodeId($deleteSerialId);
                    }
                    $item['delete_serial_ids'] = $deleteSerialIds;
                }

                if (array_key_exists('serials', $item) && is_array($item['serials'])) {
                    $serials = [];
                    foreach ($item['serials'] as $serialItem) {
                        if (! empty($serialItem['id'])) {
                            $serialItem['id'] = HashidsHelper::decodeId($serialItem['id']);
                        }
                        $serials[] = $serialItem;
                    }
                    $item['serials'] = $serials;
                }

                $inItems[] = $item;
            }
            $this->merge(['in_items' => $inItems]);
        }

        if (is_array($this->input('delete_out_item_ids'))) {
            $deleteOutItemIds = [];
            foreach ($this->input('delete_out_item_ids') as $deleteOutItemId) {
                $deleteOutItemIds[] = HashidsHelper::decodeId($deleteOutItemId);
            }
            $this->merge(['delete_out_item_ids' => $deleteOutItemIds]);
        }

        if (is_array($this->input('out_items'))) {
            $outItems = [];
            foreach ($this->input('out_items') as $item) {
                if (! empty($item['id'])) {
                    $item['id'] = HashidsHelper::decodeId($item['id']);
                }
                if (isset($item['product_unit_id'])) {
                    $item['product_unit_id'] = HashidsHelper::decodeId($item['product_unit_id']);
                }

                if (array_key_exists('delete_serial_ids', $item) && is_array($item['delete_serial_ids'])) {
                    $deleteSerialIds = [];
                    foreach ($item['delete_serial_ids'] as $deleteSerialId) {
                        $deleteSerialIds[] = HashidsHelper::decodeId($deleteSerialId);
                    }
                    $item['delete_serial_ids'] = $deleteSerialIds;
                }

                if (array_key_exists('serials', $item) && is_array($item['serials'])) {
                    $serials = [];
                    foreach ($item['serials'] as $serialItem) {
                        if (! empty($serialItem['id'])) {
                            $serialItem['id'] = HashidsHelper::decodeId($serialItem['id']);
                        }
                        $serials[] = $serialItem;
                    }
                    $item['serials'] = $serials;
                }

                $outItems[] = $item;
            }
            $this->merge(['out_items' => $outItems]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $hasInitialErrors = $validator->errors()->isNotEmpty();

            $inItems = $validator->getData()['in_items'] ?? [];
            $inItems = is_array($inItems) ? $inItems : [];
            if (! $hasInitialErrors && is_array($inItems) && ! empty($inItems)) {
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
            }

            $outItems = $validator->getData()['out_items'] ?? [];
            $outItems = is_array($outItems) ? $outItems : [];
            if (! $hasInitialErrors && is_array($outItems) && ! empty($outItems)) {
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
            }

            $inWarehouseId = $validator->getData()['in_warehouse_id'] ?? null;
            $outWarehouseId = $validator->getData()['out_warehouse_id'] ?? null;
            if (
                empty($inWarehouseId) || empty($outWarehouseId) ||
                (int) $inWarehouseId !== (int) $outWarehouseId ||
                empty($inItems) || empty($outItems)
            ) {
                return;
            }

            $allProductUnitIds = collect($inItems)->pluck('product_unit_id')
                ->concat(collect($outItems)->pluck('product_unit_id'))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $productUnitProductIds = ProductUnit::whereIn('id', $allProductUnitIds)
                ->pluck('product_id', 'id')
                ->map(fn ($productId) => (int) $productId)
                ->all();

            $inProductIdsByIndex = [];
            foreach ($inItems as $i => $item) {
                $productUnitId = $item['product_unit_id'] ?? null;
                if (empty($productUnitId) || ! isset($productUnitProductIds[$productUnitId])) {
                    continue;
                }

                $inProductIdsByIndex[$i] = $productUnitProductIds[$productUnitId];
            }

            $outProductIdsByIndex = [];
            foreach ($outItems as $i => $item) {
                $productUnitId = $item['product_unit_id'] ?? null;
                if (empty($productUnitId) || ! isset($productUnitProductIds[$productUnitId])) {
                    continue;
                }

                $outProductIdsByIndex[$i] = $productUnitProductIds[$productUnitId];
            }

            $duplicateProductIds = array_unique(array_intersect(
                array_values($inProductIdsByIndex),
                array_values($outProductIdsByIndex)
            ));

            foreach ($inProductIdsByIndex as $i => $productId) {
                if (in_array($productId, $duplicateProductIds, true)) {
                    $validator->errors()->add(
                        'in_items.'.$i.'.product_unit_id',
                        trans('validation.stock_adjustment.same_warehouse_same_product_not_allowed')
                    );
                }
            }

            foreach ($outProductIdsByIndex as $i => $productId) {
                if (in_array($productId, $duplicateProductIds, true)) {
                    $validator->errors()->add(
                        'out_items.'.$i.'.product_unit_id',
                        trans('validation.stock_adjustment.same_warehouse_same_product_not_allowed')
                    );
                }
            }

            $stockAdjustment = $this->route('stock_adjustment');
            if (! $stockAdjustment instanceof StockAdjustment) {
                return;
            }

            $stockAdjustment->loadMissing('inItems.serials', 'outItems.serials');

            $allowedInItemIds = $stockAdjustment->inItems->pluck('id')->map(fn ($id) => (int) $id)->all();
            $allowedOutItemIds = $stockAdjustment->outItems->pluck('id')->map(fn ($id) => (int) $id)->all();
            $allowedInSerialIdsByItemId = $stockAdjustment->inItems
                ->mapWithKeys(fn ($item) => [
                    (int) $item->id => $item->serials->pluck('id')->map(fn ($id) => (int) $id)->all(),
                ])
                ->all();
            $allowedOutSerialIdsByItemId = $stockAdjustment->outItems
                ->mapWithKeys(fn ($item) => [
                    (int) $item->id => $item->serials->pluck('id')->map(fn ($id) => (int) $id)->all(),
                ])
                ->all();

            foreach (($validator->getData()['delete_in_item_ids'] ?? []) as $i => $deleteInItemId) {
                if (! empty($deleteInItemId) && ! in_array((int) $deleteInItemId, $allowedInItemIds, true)) {
                    $validator->errors()->add('delete_in_item_ids.'.$i, trans('rules.stock_adjustment.invalid_in_item_reference'));
                }
            }

            foreach ($inItems as $i => $item) {
                if (! is_array($item)) {
                    continue;
                }

                $itemId = $item['id'] ?? null;
                $allowedSerialIds = [];

                if (! empty($itemId)) {
                    if (! in_array((int) $itemId, $allowedInItemIds, true)) {
                        $validator->errors()->add('in_items.'.$i.'.id', trans('rules.stock_adjustment.invalid_in_item_reference'));
                    } else {
                        $allowedSerialIds = $allowedInSerialIdsByItemId[(int) $itemId] ?? [];
                    }
                }

                foreach (($item['delete_serial_ids'] ?? []) as $j => $deleteSerialId) {
                    if (! empty($deleteSerialId) && ! in_array((int) $deleteSerialId, $allowedSerialIds, true)) {
                        $validator->errors()->add('in_items.'.$i.'.delete_serial_ids.'.$j, trans('rules.stock_adjustment.invalid_in_item_serial_reference'));
                    }
                }

                foreach (($item['serials'] ?? []) as $j => $serialItem) {
                    if (! is_array($serialItem)) {
                        continue;
                    }

                    $serialId = $serialItem['id'] ?? null;
                    if (! empty($serialId) && ! in_array((int) $serialId, $allowedSerialIds, true)) {
                        $validator->errors()->add('in_items.'.$i.'.serials.'.$j.'.id', trans('rules.stock_adjustment.invalid_in_item_serial_reference'));
                    }
                }
            }

            foreach (($validator->getData()['delete_out_item_ids'] ?? []) as $i => $deleteOutItemId) {
                if (! empty($deleteOutItemId) && ! in_array((int) $deleteOutItemId, $allowedOutItemIds, true)) {
                    $validator->errors()->add('delete_out_item_ids.'.$i, trans('rules.stock_adjustment.invalid_out_item_reference'));
                }
            }

            foreach ($outItems as $i => $item) {
                if (! is_array($item)) {
                    continue;
                }

                $itemId = $item['id'] ?? null;
                $allowedSerialIds = [];

                if (! empty($itemId)) {
                    if (! in_array((int) $itemId, $allowedOutItemIds, true)) {
                        $validator->errors()->add('out_items.'.$i.'.id', trans('rules.stock_adjustment.invalid_out_item_reference'));
                    } else {
                        $allowedSerialIds = $allowedOutSerialIdsByItemId[(int) $itemId] ?? [];
                    }
                }

                foreach (($item['delete_serial_ids'] ?? []) as $j => $deleteSerialId) {
                    if (! empty($deleteSerialId) && ! in_array((int) $deleteSerialId, $allowedSerialIds, true)) {
                        $validator->errors()->add('out_items.'.$i.'.delete_serial_ids.'.$j, trans('rules.stock_adjustment.invalid_out_item_serial_reference'));
                    }
                }

                foreach (($item['serials'] ?? []) as $j => $serialItem) {
                    if (! is_array($serialItem)) {
                        continue;
                    }

                    $serialId = $serialItem['id'] ?? null;
                    if (! empty($serialId) && ! in_array((int) $serialId, $allowedSerialIds, true)) {
                        $validator->errors()->add('out_items.'.$i.'.serials.'.$j.'.id', trans('rules.stock_adjustment.invalid_out_item_serial_reference'));
                    }
                }
            }
        });
    }
}
