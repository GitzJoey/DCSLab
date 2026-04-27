<?php

namespace App\Http\Requests\PurchaseReceipt;

use App\Enums\PurchaseReceiptModeEnum;
use App\Helpers\HashidsHelper;
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
                $item['purchase_item_id'] = ! empty($item['purchase_item_id']) ? HashidsHelper::decodeId($item['purchase_item_id']) : null;
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
            'is_from_direct_purchase' => ['required', 'boolean'],
            'code' => ['required', 'string'],
            'date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'warehouse_id' => ['required', 'integer', 'bail', new ExistsForCompany('warehouses', $this->company_id), new IsValidWarehouse($this->company_id, false)],
            'remarks' => ['present', 'nullable', 'string'],
            'is_posted' => ['required', 'boolean'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_item_id' => ['present', 'nullable', 'integer', new ExistsForCompany('purchase_items', $this->company_id)],
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
            $isFromDirectPurchase = $this->boolean('is_from_direct_purchase');

            if ($isFromDirectPurchase) {
                $validator->errors()->add('is_from_direct_purchase', trans('rules.purchase_receipt.direct_mode_is_managed_from_purchase'));
            }

            if (is_null($purchaseId)) {
                foreach ($this->input('items', []) as $index => $item) {
                    if (! empty($item['purchase_item_id'])) {
                        $validator->errors()->add("items.$index.purchase_item_id", trans('rules.purchase_receipt.purchase_item_must_be_empty_without_purchase'));
                    }
                }

                return;
            }

            $purchase = Purchase::with(['items', 'receipts'])->find($purchaseId);
            if (is_null($purchase)) {
                return;
            }

            if ($purchase->receipt_mode === PurchaseReceiptModeEnum::DIRECT) {
                $validator->errors()->add('purchase_id', trans('rules.purchase_receipt.direct_mode_is_managed_from_purchase'));

                return;
            }

            if ((int) $this->input('supplier_id') !== (int) $purchase->supplier_id) {
                $validator->errors()->add('supplier_id', trans('rules.purchase_receipt.supplier_must_match_purchase'));
            }

            if ((int) $this->input('branch_id') !== (int) $purchase->branch_id) {
                $validator->errors()->add('branch_id', trans('rules.purchase_receipt.branch_must_match_purchase'));
            }

            $purchaseItemIds = $purchase->items->pluck('id')->all();
            foreach ($this->input('items', []) as $index => $item) {
                $purchaseItemId = $item['purchase_item_id'] ?? null;

                if (is_null($purchaseItemId)) {
                    $validator->errors()->add("items.$index.purchase_item_id", trans('rules.purchase_receipt.purchase_item_is_required_with_purchase'));

                    continue;
                }

                if (! in_array($purchaseItemId, $purchaseItemIds, true)) {
                    $validator->errors()->add("items.$index.purchase_item_id", trans('rules.purchase_receipt.invalid_purchase_item_reference'));
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
            'is_from_direct_purchase' => 'is from direct purchase',
            'code' => 'code',
            'date' => 'date',
            'warehouse_id' => 'warehouse',
            'remarks' => 'remarks',
            'is_posted' => 'is posted',
            'items.*.purchase_item_id' => 'purchase item',
            'items.*.qty' => 'qty',
            'items.*.product_unit_id' => 'product unit',
            'items.*.product_unit_conversion_value' => 'product unit conversion value',
            'items.*.remarks' => 'remarks',
            'items.*.serials' => 'serials',
        ];
    }
}
