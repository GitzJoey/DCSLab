<?php

namespace App\Http\Requests\PurchaseOrderReceipt;

use App\Helpers\HashidsHelper;
use App\Models\ProductUnit;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderReceipt;
use App\Models\PurchaseOrderReceiptCost;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidDate;
use App\Rules\IsValidSupplier;
use App\Rules\IsValidWarehouse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderReceiptUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('update', $this->route('purchase_order_receipt'));
    }

    public function prepareForValidation()
    {
        /** @var PurchaseOrderReceipt $purchaseOrderReceipt */
        $purchaseOrderReceipt = $this->route('purchase_order_receipt');

        $this->merge([
            'company_id' => $purchaseOrderReceipt->company_id,
            'branch_id' => $purchaseOrderReceipt->branch_id,
            'supplier_id' => $this->filled('supplier_id') ? HashidsHelper::decodeId($this->supplier_id) : null,
            'purchase_order_id' => $this->filled('purchase_order_id') ? HashidsHelper::decodeId($this->purchase_order_id) : null,
            'warehouse_id' => $this->filled('warehouse_id') ? HashidsHelper::decodeId($this->warehouse_id) : null,
            'delete_item_ids' => collect($this->delete_item_ids ?? [])->map(fn ($id) => HashidsHelper::decodeId($id))->all(),
            'items' => collect($this->items ?? [])->map(function ($item) {
                if (! is_array($item)) {
                    return $item;
                }

                $item['id'] = ! empty($item['id']) ? HashidsHelper::decodeId($item['id']) : null;
                $item['purchase_order_item_id'] = ! empty($item['purchase_order_item_id']) ? HashidsHelper::decodeId($item['purchase_order_item_id']) : null;
                $item['product_unit_id'] = ! empty($item['product_unit_id']) ? HashidsHelper::decodeId($item['product_unit_id']) : null;
                $item['remarks'] = $item['remarks'] ?? null;
                $item['delete_serial_ids'] = collect($item['delete_serial_ids'] ?? [])->map(fn ($id) => HashidsHelper::decodeId($id))->all();
                $item['serials'] = collect($item['serials'] ?? [])->map(function ($serial) {
                    $serial['id'] = ! empty($serial['id']) ? HashidsHelper::decodeId($serial['id']) : null;

                    return $serial;
                })->all();

                return $item;
            })->all(),
            'delete_cost_ids' => collect($this->delete_cost_ids ?? [])->map(fn ($id) => HashidsHelper::decodeId($id))->all(),
            'costs' => collect($this->costs ?? [])->map(function ($cost) {
                if (! is_array($cost)) {
                    return $cost;
                }

                $cost['id'] = ! empty($cost['id']) ? HashidsHelper::decodeId($cost['id']) : null;
                $cost['cash_account_id'] = ! empty($cost['cash_account_id']) ? HashidsHelper::decodeId($cost['cash_account_id']) : null;
                $cost['remarks'] = $cost['remarks'] ?? null;

                return $cost;
            })->all(),
        ]);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer'],
            'branch_id' => ['required', 'integer'],
            'code' => ['required', 'string', 'max:255'],
            'date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'supplier_id' => ['required', 'integer', 'bail', new ExistsForCompany('suppliers', $this->company_id), new IsValidSupplier($this->company_id)],
            'purchase_order_id' => ['required', 'integer', 'bail', new ExistsForCompany('purchase_orders', $this->company_id)],
            'warehouse_id' => ['required', 'integer', 'bail', new ExistsForCompany('warehouses', $this->company_id), new IsValidWarehouse($this->company_id, false)],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],
            'is_posted' => ['required', 'boolean'],

            'delete_item_ids' => ['present', 'array'],
            'delete_item_ids.*' => ['required', 'integer', new ExistsForCompany('purchase_order_receipt_items', $this->company_id)],

            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('purchase_order_receipt_items', $this->company_id)],
            'items.*.purchase_order_item_id' => ['present', 'nullable', 'integer', new ExistsForCompany('purchase_order_items', $this->company_id)],
            'items.*.qty' => ['required', 'numeric', 'gt:0'],
            'items.*.product_unit_id' => ['required', 'integer', new ExistsForCompany('product_units', $this->company_id)],
            'items.*.product_unit_conversion_value' => ['required', 'numeric', 'gt:0'],
            'items.*.remarks' => ['present', 'nullable', 'string', 'max:255'],

            'items.*.delete_serial_ids' => ['present', 'array'],
            'items.*.delete_serial_ids.*' => ['required', 'integer', new ExistsForCompany('purchase_order_receipt_item_serials', $this->company_id)],
            'items.*.serials' => ['present', 'array'],
            'items.*.serials.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('purchase_order_receipt_item_serials', $this->company_id)],
            'items.*.serials.*.serial' => ['required', 'string', 'max:255'],

            'delete_cost_ids' => ['present', 'array'],
            'delete_cost_ids.*' => ['required', 'integer', new ExistsForCompany('purchase_order_receipt_costs', $this->company_id)],

            'costs' => ['present', 'array'],
            'costs.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('purchase_order_receipt_costs', $this->company_id)],
            'costs.*.code' => ['required', 'string', 'max:255'],
            'costs.*.date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'costs.*.name' => ['required', 'string', 'max:255'],
            'costs.*.cash_account_id' => [
                'required',
                'integer',
                'bail',
                new ExistsForCompany('cash_accounts', $this->company_id),
                new IsValidCashAccount($this->branch_id),
            ],
            'costs.*.amount' => ['required', 'numeric', 'gt:0'],
            'costs.*.remarks' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validatePurchaseOrderLinkage($validator);
            $this->validateDuplicateProducts($validator);
            $this->validateChildOwnership($validator);
            $this->validateSerials($validator);
            $this->validateDuplicateSerialsInDocument($validator, $validator->getData()['items'] ?? []);
            $this->validateCostCodes($validator);
        });
    }

    protected function validatePurchaseOrderLinkage($validator): void
    {
        $purchaseOrderId = $this->input('purchase_order_id');

        if (empty($purchaseOrderId)) {
            return;
        }

        $purchaseOrder = PurchaseOrder::with('items:id,purchase_order_id')->find($purchaseOrderId);
        if (is_null($purchaseOrder)) {
            return;
        }

        if (is_null($purchaseOrder->supplier_id)) {
            $validator->errors()->add('purchase_order_id', trans('rules.purchase_order_receipt.purchase_order_must_have_supplier'));
        } elseif ($this->filled('supplier_id') && (int) $this->input('supplier_id') !== (int) $purchaseOrder->supplier_id) {
            $validator->errors()->add('supplier_id', trans('rules.purchase_order_receipt.supplier_must_match_purchase_order'));
        }

        if ($this->filled('branch_id') && (int) $this->input('branch_id') !== (int) $purchaseOrder->branch_id) {
            $validator->errors()->add('purchase_order_id', trans('rules.purchase_order_receipt.branch_must_match_purchase_order'));
        }

        $purchaseOrderItemIds = $purchaseOrder->items->pluck('id')->all();
        foreach ($this->input('items', []) as $index => $item) {
            $purchaseOrderItemId = $item['purchase_order_item_id'] ?? null;

            if (! is_null($purchaseOrderItemId) && ! in_array((int) $purchaseOrderItemId, $purchaseOrderItemIds, true)) {
                $validator->errors()->add(
                    "items.$index.purchase_order_item_id",
                    trans('rules.purchase_order_receipt.invalid_purchase_order_item_reference')
                );
            }
        }
    }

    protected function validateDuplicateProducts($validator): void
    {
        $items = $this->input('items', []);
        $productUnitIds = collect($items)
            ->pluck('product_unit_id')
            ->filter(fn ($productUnitId) => is_numeric($productUnitId))
            ->map(fn ($productUnitId) => (int) $productUnitId)
            ->unique()
            ->values();

        if ($productUnitIds->isEmpty()) {
            return;
        }

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
                $validator->errors()->add("items.$index.product_unit_id", trans('rules.purchase_order_receipt.duplicate_product'));

                continue;
            }

            $seenProductIds[$productId] = true;
        }
    }

    protected function validateChildOwnership($validator): void
    {
        /** @var PurchaseOrderReceipt $purchaseOrderReceipt */
        $purchaseOrderReceipt = $this->route('purchase_order_receipt');

        $purchaseOrderReceiptItems = $purchaseOrderReceipt->items()
            ->with('serials:id,purchase_order_receipt_item_id')
            ->get()
            ->keyBy('id');
        $purchaseOrderReceiptItemIds = $purchaseOrderReceiptItems->keys()->all();
        $purchaseOrderReceiptCostIds = $purchaseOrderReceipt->costs()->pluck('id')->all();

        foreach ($this->input('delete_item_ids', []) as $index => $deleteItemId) {
            if (! in_array($deleteItemId, $purchaseOrderReceiptItemIds, true)) {
                $validator->errors()->add(
                    "delete_item_ids.$index",
                    trans('rules.purchase_order_receipt.invalid_delete_item_reference')
                );
            }
        }

        foreach ($this->input('items', []) as $index => $item) {
            $itemId = $item['id'] ?? null;
            $purchaseOrderReceiptItem = ! is_null($itemId) ? $purchaseOrderReceiptItems->get($itemId) : null;

            if (! is_null($itemId) && is_null($purchaseOrderReceiptItem)) {
                $validator->errors()->add(
                    "items.$index.id",
                    trans('rules.purchase_order_receipt.invalid_item_reference')
                );
            }

            $purchaseOrderReceiptItemSerialIds = $purchaseOrderReceiptItem?->serials->pluck('id')->all() ?? [];
            foreach ($item['delete_serial_ids'] ?? [] as $serialIndex => $serialId) {
                if (! in_array($serialId, $purchaseOrderReceiptItemSerialIds, true)) {
                    $validator->errors()->add(
                        "items.$index.delete_serial_ids.$serialIndex",
                        trans('rules.purchase_order_receipt.invalid_serial_reference')
                    );
                }
            }

            foreach ($item['serials'] ?? [] as $serialIndex => $serial) {
                $serialId = $serial['id'] ?? null;

                if (! is_null($serialId) && ! in_array($serialId, $purchaseOrderReceiptItemSerialIds, true)) {
                    $validator->errors()->add(
                        "items.$index.serials.$serialIndex.id",
                        trans('rules.purchase_order_receipt.invalid_serial_reference')
                    );
                }
            }
        }

        foreach ($this->input('delete_cost_ids', []) as $index => $deleteCostId) {
            if (! in_array($deleteCostId, $purchaseOrderReceiptCostIds, true)) {
                $validator->errors()->add(
                    "delete_cost_ids.$index",
                    trans('rules.purchase_order_receipt.invalid_delete_cost_reference')
                );
            }
        }

        foreach ($this->input('costs', []) as $index => $cost) {
            $costId = $cost['id'] ?? null;

            if (! is_null($costId) && ! in_array($costId, $purchaseOrderReceiptCostIds, true)) {
                $validator->errors()->add(
                    "costs.$index.id",
                    trans('rules.purchase_order_receipt.invalid_cost_reference')
                );
            }
        }
    }

    protected function validateSerials($validator): void
    {
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
                $validator->errors()->add('items.'.$index.'.serials', trans('rules.purchase_order_receipt.duplicate_serial'));
            }

            $serialCount = (string) count(is_array($serials) ? $serials : []);
            if (bccomp($serialCount, $baseQty, 8) !== 0) {
                $validator->errors()->add('items.'.$index.'.serials', trans('rules.stock_transfer.serial_count_must_match_base_qty'));
            }
        }
    }

    /**
     * A serial may only appear once across ALL items of the document, not just
     * within a single item.
     */
    protected function validateDuplicateSerialsInDocument($validator, array $items): void
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
                        trans('rules.purchase_order_receipt.duplicate_serial_in_document')
                    );

                    continue;
                }

                $seenSerials[$serialValue] = true;
            }
        }
    }

    protected function validateCostCodes($validator): void
    {
        $autoKeyword = config('dcslab.KEYWORDS.AUTO');
        $seenCodes = [];

        foreach ($this->input('costs', []) as $index => $cost) {
            $code = $cost['code'] ?? null;
            $costId = $cost['id'] ?? null;

            if (! is_string($code) || $code === '' || $code === $autoKeyword) {
                continue;
            }

            if (isset($seenCodes[$code])) {
                $validator->errors()->add("costs.$index.code", trans('rules.unique_code'));

                continue;
            }

            $seenCodes[$code] = true;

            $query = PurchaseOrderReceiptCost::query()
                ->where('company_id', $this->input('company_id'))
                ->where('code', $code);

            if (! empty($costId)) {
                $query->where('id', '<>', $costId);
            }

            if ($query->exists()) {
                $validator->errors()->add("costs.$index.code", trans('rules.unique_code'));
            }
        }
    }

    public function attributes()
    {
        return array_merge(
            trans('validation_attributes.purchase_order_receipt'),
            [
                'items.*.id' => trans('validation_attributes.purchase_order_receipt_item.id'),
                'items.*.purchase_order_item_id' => trans('validation_attributes.purchase_order_receipt_item.purchase_order_item_id'),
                'items.*.qty' => trans('validation_attributes.purchase_order_receipt_item.qty'),
                'items.*.product_unit_id' => trans('validation_attributes.purchase_order_receipt_item.product_unit_id'),
                'items.*.product_unit_conversion_value' => trans('validation_attributes.purchase_order_receipt_item.product_unit_conversion_value'),
                'items.*.remarks' => trans('validation_attributes.purchase_order_receipt_item.remarks'),
                'items.*.serials.*.id' => trans('validation_attributes.purchase_order_receipt_item_serial.id'),
                'items.*.serials.*.serial' => trans('validation_attributes.purchase_order_receipt_item_serial.serial'),
                'costs.*.id' => trans('validation_attributes.purchase_order_receipt_cost.id'),
                'costs.*.code' => trans('validation_attributes.purchase_order_receipt_cost.code'),
                'costs.*.date' => trans('validation_attributes.purchase_order_receipt_cost.date'),
                'costs.*.name' => trans('validation_attributes.purchase_order_receipt_cost.name'),
                'costs.*.cash_account_id' => trans('validation_attributes.purchase_order_receipt_cost.cash_account_id'),
                'costs.*.amount' => trans('validation_attributes.purchase_order_receipt_cost.amount'),
                'costs.*.remarks' => trans('validation_attributes.purchase_order_receipt_cost.remarks'),
            ],
        );
    }
}
