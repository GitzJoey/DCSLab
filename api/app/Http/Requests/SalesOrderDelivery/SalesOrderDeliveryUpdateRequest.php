<?php

namespace App\Http\Requests\SalesOrderDelivery;

use App\Helpers\HashidsHelper;
use App\Models\ProductUnit;
use App\Models\SalesOrder;
use App\Models\SalesOrderDelivery;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidDate;
use App\Rules\IsValidWarehouse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SalesOrderDeliveryUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) return false;

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('update', $this->route('sales_order_delivery'));
    }

    public function prepareForValidation()
    {
        $this->merge([
            'customer_id' => $this->filled('customer_id') ? HashidsHelper::decodeId($this->customer_id) : null,
            'sales_order_id' => $this->filled('sales_order_id') ? HashidsHelper::decodeId($this->sales_order_id) : null,
            'warehouse_id' => $this->filled('warehouse_id') ? HashidsHelper::decodeId($this->warehouse_id) : null,
            'delete_item_ids' => collect($this->delete_item_ids ?? [])->map(fn ($id) => HashidsHelper::decodeId($id))->all(),
            'items' => collect($this->items ?? [])->map(function ($item) {
                $item['id'] = ! empty($item['id']) ? HashidsHelper::decodeId($item['id']) : null;
                $item['product_unit_id'] = ! empty($item['product_unit_id']) ? HashidsHelper::decodeId($item['product_unit_id']) : null;
                $item['delete_serial_ids'] = collect($item['delete_serial_ids'] ?? [])->map(fn ($id) => HashidsHelper::decodeId($id))->all();
                $item['serials'] = collect($item['serials'] ?? [])->map(function ($serial) {
                    $serial['id'] = ! empty($serial['id']) ? HashidsHelper::decodeId($serial['id']) : null;

                    return $serial;
                })->all();

                return $item;
            })->all(),
            'delete_cost_ids' => collect($this->delete_cost_ids ?? [])->map(fn ($id) => HashidsHelper::decodeId($id))->all(),
            'costs' => collect($this->costs ?? [])->map(function ($cost) {
                $cost['id'] = ! empty($cost['id']) ? HashidsHelper::decodeId($cost['id']) : null;
                $cost['cash_account_id'] = ! empty($cost['cash_account_id']) ? HashidsHelper::decodeId($cost['cash_account_id']) : null;

                return $cost;
            })->all(),
        ]);
    }

    public function rules()
    {
        /** @var SalesOrderDelivery $salesOrderDelivery */
        $salesOrderDelivery = $this->route('sales_order_delivery');

        return [
            'customer_id' => ['required', 'integer', 'bail', new ExistsForCompany('customers', $salesOrderDelivery->company_id)],
            'sales_order_id' => ['required', 'integer', new ExistsForCompany('sales_orders', $salesOrderDelivery->company_id)],
            'code' => ['required', 'string'],
            'date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'warehouse_id' => ['required', 'integer', 'bail', new ExistsForCompany('warehouses', $salesOrderDelivery->company_id), new IsValidWarehouse($salesOrderDelivery->company_id, false)],
            'remarks' => ['present', 'nullable', 'string'],
            'is_posted' => ['required', 'boolean'],

            'delete_item_ids' => ['present', 'array'],
            'delete_item_ids.*' => ['required', 'integer', new ExistsForCompany('sales_order_delivery_items', $salesOrderDelivery->company_id)],

            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('sales_order_delivery_items', $salesOrderDelivery->company_id)],
            'items.*.qty' => ['required', 'numeric', 'gt:0'],
            'items.*.product_unit_id' => ['required', 'integer', new ExistsForCompany('product_units', $salesOrderDelivery->company_id)],
            'items.*.product_unit_conversion_value' => ['required', 'numeric', 'gt:0'],
            'items.*.remarks' => ['present', 'nullable', 'string'],

            'items.*.delete_serial_ids' => ['present', 'array'],
            'items.*.delete_serial_ids.*' => ['required', 'integer', new ExistsForCompany('sales_order_delivery_item_serials', $salesOrderDelivery->company_id)],
            'items.*.serials' => ['present', 'array'],
            'items.*.serials.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('sales_order_delivery_item_serials', $salesOrderDelivery->company_id)],
            'items.*.serials.*.serial' => ['required', 'string', 'max:255'],

            'delete_cost_ids' => ['present', 'array'],
            'delete_cost_ids.*' => ['required', 'integer', new ExistsForCompany('sales_order_delivery_costs', $salesOrderDelivery->company_id)],

            'costs' => ['present', 'array'],
            'costs.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('sales_order_delivery_costs', $salesOrderDelivery->company_id)],
            'costs.*.code' => ['required', 'string'],
            'costs.*.date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'costs.*.name' => ['required', 'string', 'max:255'],
            'costs.*.cash_account_id' => [
                'required',
                'integer',
                'bail',
                new ExistsForCompany('cash_accounts', $salesOrderDelivery->company_id),
                new IsValidCashAccount($salesOrderDelivery->branch_id),
            ],
            'costs.*.amount' => ['required', 'numeric', 'gt:0'],
            'costs.*.remarks' => ['present', 'nullable', 'string'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);
            $productUnitIds = collect($items)
                ->pluck('product_unit_id')
                ->filter(fn ($productUnitId) => is_numeric($productUnitId))
                ->map(fn ($productUnitId) => (int) $productUnitId)
                ->unique()
                ->values();

            if ($productUnitIds->isNotEmpty()) {
                $productIdsByProductUnitId = ProductUnit::query()
                    ->whereIn('id', $productUnitIds)
                    ->pluck('product_id', 'id')
                    ->map(fn ($productId) => (int) $productId)
                    ->all();

                $seenProductIds = [];
                foreach ($items as $index => $item) {
                    $productUnitId = $item['product_unit_id'] ?? null;
                    if (! is_numeric($productUnitId)) {
                        continue;
                    }

                    $productId = $productIdsByProductUnitId[(int) $productUnitId] ?? null;
                    if (is_null($productId)) {
                        continue;
                    }

                    if (isset($seenProductIds[$productId])) {
                        $validator->errors()->add("items.$index.product_unit_id", trans('rules.sales_order_delivery.duplicate_product'));

                        continue;
                    }

                    $seenProductIds[$productId] = true;
                }
            }

            /** @var SalesOrderDelivery $salesOrderDelivery */
            $salesOrderDelivery = $this->route('sales_order_delivery');
            $salesOrderId = $this->input('sales_order_id');

            if (! is_null($salesOrderId)) {
                $salesOrder = SalesOrder::find($salesOrderId);
                if (! is_null($salesOrder)) {
                    if (is_null($salesOrder->customer_id)) {
                        $validator->errors()->add('sales_order_id', trans('rules.sales_order_delivery.sales_order_must_have_customer'));
                    } elseif ((int) $this->input('customer_id') !== (int) $salesOrder->customer_id) {
                        $validator->errors()->add('customer_id', trans('rules.sales_order_delivery.customer_must_match_sales_order'));
                    }

                    if ((int) $salesOrderDelivery->branch_id !== (int) $salesOrder->branch_id) {
                        $validator->errors()->add('sales_order_id', trans('rules.sales_order_delivery.branch_must_match_sales_order'));
                    }
                }
            }

            $salesOrderDeliveryItems = $salesOrderDelivery->items()
                ->with('serials:id,sales_order_delivery_item_id')
                ->get()
                ->keyBy('id');
            $salesOrderDeliveryItemIds = $salesOrderDeliveryItems->keys()->all();

            foreach ($this->input('delete_item_ids', []) as $index => $deleteItemId) {
                if (! in_array($deleteItemId, $salesOrderDeliveryItemIds, true)) {
                    $validator->errors()->add(
                        "delete_item_ids.$index",
                        trans('rules.sales_order_delivery.invalid_delete_item_reference')
                    );
                }
            }

            foreach ($this->input('items', []) as $index => $item) {
                $itemId = $item['id'] ?? null;
                $salesOrderDeliveryItem = ! is_null($itemId) ? $salesOrderDeliveryItems->get($itemId) : null;

                if (! is_null($itemId) && is_null($salesOrderDeliveryItem)) {
                    $validator->errors()->add(
                        "items.$index.id",
                        trans('rules.sales_order_delivery.invalid_item_reference')
                    );
                }

                $salesOrderDeliveryItemSerialIds = $salesOrderDeliveryItem?->serials->pluck('id')->all() ?? [];
                foreach ($item['delete_serial_ids'] ?? [] as $serialIndex => $serialId) {
                    if (! in_array($serialId, $salesOrderDeliveryItemSerialIds, true)) {
                        $validator->errors()->add(
                            "items.$index.delete_serial_ids.$serialIndex",
                            trans('rules.sales_order_delivery.invalid_serial_reference')
                        );
                    }
                }

                foreach ($item['serials'] ?? [] as $serialIndex => $serial) {
                    $serialId = $serial['id'] ?? null;

                    if (! is_null($serialId) && ! in_array($serialId, $salesOrderDeliveryItemSerialIds, true)) {
                        $validator->errors()->add(
                            "items.$index.serials.$serialIndex.id",
                            trans('rules.sales_order_delivery.invalid_serial_reference')
                        );
                    }
                }
            }

            $salesOrderDeliveryCostIds = $salesOrderDelivery->costs()->pluck('id')->all();

            foreach ($this->input('delete_cost_ids', []) as $index => $deleteCostId) {
                if (! in_array($deleteCostId, $salesOrderDeliveryCostIds, true)) {
                    $validator->errors()->add(
                        "delete_cost_ids.$index",
                        trans('rules.sales_order_delivery.invalid_delete_cost_reference')
                    );
                }
            }

            foreach ($this->input('costs', []) as $index => $cost) {
                $costId = $cost['id'] ?? null;

                if (! is_null($costId) && ! in_array($costId, $salesOrderDeliveryCostIds, true)) {
                    $validator->errors()->add(
                        "costs.$index.id",
                        trans('rules.sales_order_delivery.invalid_cost_reference')
                    );
                }
            }

            foreach (($validator->getData()['items'] ?? []) as $index => $item) {
                $productUnitId = $item['product_unit_id'] ?? null;
                $qty = $item['qty'] ?? null;
                $conversionValue = $item['product_unit_conversion_value'] ?? null;
                $serials = $item['serials'] ?? [];

                if (empty($productUnitId) || ! is_numeric($qty) || ! is_numeric($conversionValue)) {
                    continue;
                }

                $product = ProductUnit::with('product')->find($productUnitId)?->product;
                if (! $product?->is_use_serial_number) {
                    continue;
                }

                $baseQty = bcmul((string) $qty, (string) $conversionValue, 8);
                $normalizedBaseQty = rtrim(rtrim($baseQty, '0'), '.');
                if (str_contains($normalizedBaseQty, '.')) {
                    $validator->errors()->add('items.'.$index.'.serials', trans('rules.stock_transfer.serial_base_qty_must_be_integer'));

                    continue;
                }

                $serialValues = collect(is_array($serials) ? $serials : [])
                    ->pluck('serial')
                    ->values();
                if ($serialValues->count() !== $serialValues->unique()->count()) {
                    $validator->errors()->add('items.'.$index.'.serials', trans('rules.sales_order_delivery.duplicate_serial'));
                }

                $serialCount = (string) count(is_array($serials) ? $serials : []);
                if (bccomp($serialCount, $baseQty, 8) !== 0) {
                    $validator->errors()->add('items.'.$index.'.serials', trans('rules.stock_transfer.serial_count_must_match_base_qty'));
                }
            }

            $this->validateDuplicateSerialsInDocument($validator, $validator->getData()['items'] ?? []);
        });
    }

    /**
     * A serial may only appear once across ALL items of the document, not just
     * within a single item.
     */
    private function validateDuplicateSerialsInDocument($validator, array $items): void
    {
        $seenSerials = [];

        foreach ($items as $index => $item) {
            $serials = $item['serials'] ?? [];

            if (! is_array($serials)) {
                continue;
            }

            foreach ($serials as $serialIndex => $serial) {
                $serialValue = is_array($serial) ? ($serial['serial'] ?? null) : null;

                if (! is_string($serialValue) || $serialValue === '') {
                    continue;
                }

                if (isset($seenSerials[$serialValue])) {
                    $validator->errors()->add(
                        "items.$index.serials.$serialIndex.serial",
                        trans('rules.sales_order_delivery.duplicate_serial_in_document')
                    );

                    continue;
                }

                $seenSerials[$serialValue] = true;
            }
        }
    }

    public function attributes()
    {
        return array_merge(
            trans('validation_attributes.sales_order_delivery'),
            [
                'items.*.id' => trans('validation_attributes.sales_order_delivery_item.id'),
                'items.*.qty' => trans('validation_attributes.sales_order_delivery_item.qty'),
                'items.*.product_unit_id' => trans('validation_attributes.sales_order_delivery_item.product_unit_id'),
                'items.*.product_unit_conversion_value' => trans('validation_attributes.sales_order_delivery_item.product_unit_conversion_value'),
                'items.*.remarks' => trans('validation_attributes.sales_order_delivery_item.remarks'),
                'items.*.delete_serial_ids' => 'delete serial ids',
                'items.*.serials' => 'serials',
                'items.*.serials.*.id' => trans('validation_attributes.sales_order_delivery_item_serial.id'),
                'items.*.serials.*.serial' => trans('validation_attributes.sales_order_delivery_item_serial.serial'),
                'costs.*.id' => trans('validation_attributes.sales_order_delivery_cost.id'),
                'costs.*.code' => trans('validation_attributes.sales_order_delivery_cost.code'),
                'costs.*.date' => trans('validation_attributes.sales_order_delivery_cost.date'),
                'costs.*.name' => trans('validation_attributes.sales_order_delivery_cost.name'),
                'costs.*.cash_account_id' => trans('validation_attributes.sales_order_delivery_cost.cash_account_id'),
                'costs.*.amount' => trans('validation_attributes.sales_order_delivery_cost.amount'),
                'costs.*.remarks' => trans('validation_attributes.sales_order_delivery_cost.remarks'),
            ],
        );
    }
}
