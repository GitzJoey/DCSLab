<?php

namespace App\Http\Requests\PurchaseReceipt;

use App\Enums\PurchaseReceiptModeEnum;
use App\Helpers\HashidsHelper;
use App\Models\ProductUnit;
use App\Models\Purchase;
use App\Models\PurchaseReceipt;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidDate;
use App\Rules\IsValidSupplier;
use App\Rules\IsValidWarehouse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseReceiptUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) return false;

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('update', $this->route('purchase_receipt'));
    }

    public function prepareForValidation()
    {
        $this->merge([
            'supplier_id' => $this->filled('supplier_id') ? HashidsHelper::decodeId($this->supplier_id) : null,
            'purchase_id' => $this->filled('purchase_id') ? HashidsHelper::decodeId($this->purchase_id) : null,
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
        ]);
    }

    public function rules()
    {
        /** @var PurchaseReceipt $purchaseReceipt */
        $purchaseReceipt = $this->route('purchase_receipt');

        return [
            'supplier_id' => ['required', 'integer', 'bail', new ExistsForCompany('suppliers', $purchaseReceipt->company_id), new IsValidSupplier($purchaseReceipt->company_id)],
            'purchase_id' => ['present', 'nullable', 'integer', new ExistsForCompany('purchases', $purchaseReceipt->company_id)],
            'code' => ['required', 'string'],
            'date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'warehouse_id' => ['required', 'integer', 'bail', new ExistsForCompany('warehouses', $purchaseReceipt->company_id), new IsValidWarehouse($purchaseReceipt->company_id, false)],
            'remarks' => ['present', 'nullable', 'string'],
            'is_posted' => ['required', 'boolean'],

            'delete_item_ids' => ['present', 'array'],
            'delete_item_ids.*' => ['required', 'integer', new ExistsForCompany('purchase_receipt_items', $purchaseReceipt->company_id)],

            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('purchase_receipt_items', $purchaseReceipt->company_id)],
            'items.*.qty' => ['required', 'numeric', 'gt:0'],
            'items.*.product_unit_id' => ['required', 'integer', new ExistsForCompany('product_units', $purchaseReceipt->company_id)],
            'items.*.product_unit_conversion_value' => ['required', 'numeric', 'gt:0'],
            'items.*.remarks' => ['present', 'nullable', 'string'],

            'items.*.delete_serial_ids' => ['present', 'array'],
            'items.*.delete_serial_ids.*' => ['required', 'integer', new ExistsForCompany('purchase_receipt_item_serials', $purchaseReceipt->company_id)],
            'items.*.serials' => ['present', 'array'],
            'items.*.serials.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('purchase_receipt_item_serials', $purchaseReceipt->company_id)],
            'items.*.serials.*.serial' => ['required', 'string', 'max:255'],
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
                        $validator->errors()->add("items.$index.product_unit_id", trans('rules.purchase_receipt.duplicate_product'));

                        continue;
                    }

                    $seenProductIds[$productId] = true;
                }
            }

            /** @var PurchaseReceipt $purchaseReceipt */
            $purchaseReceipt = $this->route('purchase_receipt');
            $purchaseId = $this->input('purchase_id');
            $purchaseReceiptItems = $purchaseReceipt->items()
                ->with('serials:id,purchase_receipt_item_id')
                ->get()
                ->keyBy('id');
            $purchaseReceiptItemIds = $purchaseReceiptItems->keys()->all();

            if ($purchaseReceipt->is_from_direct_purchase) {
                $validator->errors()->add('purchase_id', trans('rules.purchase_receipt.direct_mode_is_managed_from_purchase'));

                return;
            }

            if (! is_null($purchaseId)) {
                $purchase = Purchase::with(['items', 'receipts'])->find($purchaseId);
                if (! is_null($purchase)) {
                    if ($purchase->receipt_mode === PurchaseReceiptModeEnum::DIRECT) {
                        $validator->errors()->add('purchase_id', trans('rules.purchase_receipt.direct_mode_is_managed_from_purchase'));
                    }

                    if ((int) $this->input('supplier_id') !== (int) $purchase->supplier_id) {
                        $validator->errors()->add('supplier_id', trans('rules.purchase_receipt.supplier_must_match_purchase'));
                    }

                    if ((int) $purchaseReceipt->branch_id !== (int) $purchase->branch_id) {
                        $validator->errors()->add('purchase_id', trans('rules.purchase_receipt.purchase_branch_must_match_receipt_branch'));
                    }
                }
            }

            foreach ($this->input('delete_item_ids', []) as $index => $deleteItemId) {
                if (! in_array($deleteItemId, $purchaseReceiptItemIds, true)) {
                    $validator->errors()->add(
                        "delete_item_ids.$index",
                        trans('rules.purchase_receipt.invalid_delete_item_reference')
                    );
                }
            }

            foreach ($this->input('items', []) as $index => $item) {
                $itemId = $item['id'] ?? null;
                $purchaseReceiptItem = ! is_null($itemId) ? $purchaseReceiptItems->get($itemId) : null;

                if (! is_null($itemId) && is_null($purchaseReceiptItem)) {
                    $validator->errors()->add(
                        "items.$index.id",
                        trans('rules.purchase_receipt.invalid_item_reference')
                    );
                }

                $purchaseReceiptItemSerialIds = $purchaseReceiptItem?->serials->pluck('id')->all() ?? [];
                foreach ($item['delete_serial_ids'] ?? [] as $serialIndex => $serialId) {
                    if (! in_array($serialId, $purchaseReceiptItemSerialIds, true)) {
                        $validator->errors()->add(
                            "items.$index.delete_serial_ids.$serialIndex",
                            trans('rules.purchase_receipt.invalid_serial_reference')
                        );
                    }
                }

                foreach ($item['serials'] ?? [] as $serialIndex => $serial) {
                    $serialId = $serial['id'] ?? null;

                    if (! is_null($serialId) && ! in_array($serialId, $purchaseReceiptItemSerialIds, true)) {
                        $validator->errors()->add(
                            "items.$index.serials.$serialIndex.id",
                            trans('rules.purchase_receipt.invalid_serial_reference')
                        );
                    }
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
                    $validator->errors()->add('items.'.$index.'.serials', trans('rules.purchase_receipt.duplicate_serial'));
                }

                $serialCount = (string) count(is_array($serials) ? $serials : []);
                if (bccomp($serialCount, $baseQty, 8) !== 0) {
                    $validator->errors()->add('items.'.$index.'.serials', trans('rules.stock_transfer.serial_count_must_match_base_qty'));
                }
            }
        });
    }

    public function attributes()
    {
        return [
            'supplier_id' => 'supplier',
            'purchase_id' => 'purchase',
            'code' => 'code',
            'date' => 'date',
            'warehouse_id' => 'warehouse',
            'remarks' => 'remarks',
            'is_posted' => 'is posted',
            'items.*.qty' => 'qty',
            'items.*.product_unit_id' => 'product unit',
            'items.*.product_unit_conversion_value' => 'product unit conversion value',
            'items.*.remarks' => 'remarks',
            'items.*.delete_serial_ids' => 'delete serial ids',
            'items.*.serials' => 'serials',
        ];
    }
}
