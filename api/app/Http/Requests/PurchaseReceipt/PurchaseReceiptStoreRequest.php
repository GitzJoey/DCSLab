<?php

namespace App\Http\Requests\PurchaseReceipt;

use App\Enums\PurchaseReceiptModeEnum;
use App\Helpers\HashidsHelper;
use App\Models\ProductUnit;
use App\Models\Purchase;
use App\Models\PurchaseReceipt;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidDate;
use App\Rules\IsValidSupplier;
use App\Rules\IsValidWarehouse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseReceiptStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) return false;

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', PurchaseReceipt::class);
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'supplier_id' => $this->filled('supplier_id') ? HashidsHelper::decodeId($this->supplier_id) : null,
            'purchase_id' => $this->filled('purchase_id') ? HashidsHelper::decodeId($this->purchase_id) : null,
            'warehouse_id' => $this->filled('warehouse_id') ? HashidsHelper::decodeId($this->warehouse_id) : null,
            'items' => collect($this->items ?? [])->map(function ($item) {
                $item['product_unit_id'] = ! empty($item['product_unit_id']) ? HashidsHelper::decodeId($item['product_unit_id']) : null;

                return $item;
            })->all(),
        ]);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', 'bail', new IsValidBranch($this->company_id, true)],
            'supplier_id' => ['required', 'integer', 'bail', new ExistsForCompany('suppliers', $this->company_id), new IsValidSupplier($this->company_id)],
            'purchase_id' => ['present', 'nullable', 'integer', new ExistsForCompany('purchases', $this->company_id)],
            'code' => ['required', 'string'],
            'date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'warehouse_id' => ['required', 'integer', 'bail', new ExistsForCompany('warehouses', $this->company_id), new IsValidWarehouse($this->company_id, false)],
            'remarks' => ['present', 'nullable', 'string'],
            'is_posted' => ['required', 'boolean'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.qty' => ['required', 'numeric', 'gt:0'],
            'items.*.product_unit_id' => ['required', 'integer', new ExistsForCompany('product_units', $this->company_id)],
            'items.*.product_unit_conversion_value' => ['required', 'numeric', 'gt:0'],
            'items.*.remarks' => ['present', 'nullable', 'string'],
            'items.*.serials' => ['present', 'array'],
            'items.*.serials.*.serial' => ['required', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $purchaseId = $this->input('purchase_id');

            if (! is_null($purchaseId)) {
                $purchase = Purchase::with(['items', 'receipts'])->find($purchaseId);
                if (! is_null($purchase)) {
                    if ($purchase->receipt_mode === PurchaseReceiptModeEnum::DIRECT) {
                        $validator->errors()->add('purchase_id', trans('rules.purchase_receipt.direct_mode_is_managed_from_purchase'));
                    }

                    if ((int) $this->input('supplier_id') !== (int) $purchase->supplier_id) {
                        $validator->errors()->add('supplier_id', trans('rules.purchase_receipt.supplier_must_match_purchase'));
                    }

                    if ((int) $this->input('branch_id') !== (int) $purchase->branch_id) {
                        $validator->errors()->add('branch_id', trans('rules.purchase_receipt.branch_must_match_purchase'));
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
            'company_id' => 'company',
            'branch_id' => 'branch',
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
            'items.*.serials' => 'serials',
        ];
    }
}
