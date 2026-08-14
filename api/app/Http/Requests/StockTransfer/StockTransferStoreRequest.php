<?php

namespace App\Http\Requests\StockTransfer;

use App\Helpers\HashidsHelper;
use App\Models\ProductUnit;
use App\Models\StockTransfer;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidDate;
use App\Rules\IsValidWarehouse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StockTransferStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', StockTransfer::class) ? true : false;
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'code' => ['required', 'string', 'max:255'],
            'date' => ['required', 'string', new IsValidDate('Y-m-d H:i:s')],
            'source_warehouse_id' => ['required', 'integer', new IsValidWarehouse($this->company_id, false)],
            'destination_warehouse_id' => ['required', 'integer', 'different:source_warehouse_id', new IsValidWarehouse($this->company_id, false)],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],
            'is_posted' => ['required', 'boolean'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.qty' => ['required', 'numeric', 'min:1'],
            'items.*.product_unit_id' => ['required', 'integer', 'distinct', new ExistsForCompany('product_units', $this->company_id)],
            'items.*.product_unit_conversion_value' => ['required', 'numeric', 'min:1'],
            'items.*.remarks' => ['present', 'nullable', 'string', 'max:255'],
            'items.*.serials' => ['present', 'array'],
            'items.*.serials.*.serial' => ['required', 'distinct', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.stock_transfer.company_id'),
            'branch_id' => trans('validation_attributes.stock_transfer.branch_id'),
            'code' => trans('validation_attributes.stock_transfer.code'),
            'date' => trans('validation_attributes.stock_transfer.date'),
            'source_warehouse_id' => trans('validation_attributes.stock_transfer.source_warehouse_id'),
            'destination_warehouse_id' => trans('validation_attributes.stock_transfer.destination_warehouse_id'),
            'remarks' => trans('validation_attributes.stock_transfer.remarks'),
            'is_posted' => trans('validation_attributes.stock_transfer.is_posted'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'source_warehouse_id' => $this->filled('source_warehouse_id') ? HashidsHelper::decodeId($this->source_warehouse_id) : null,
            'destination_warehouse_id' => $this->filled('destination_warehouse_id') ? HashidsHelper::decodeId($this->destination_warehouse_id) : null,
            'remarks' => $this->filled('remarks') ? $this['remarks'] : null,
        ]);

        if (is_array($this->input('items'))) {
            $productUnits = [];
            foreach ($this->input('items') as $item) {
                if (isset($item['product_unit_id'])) {
                    $item['product_unit_id'] = HashidsHelper::decodeId($item['product_unit_id']);
                }
                $item['remarks'] = $item['remarks'] ?? null;
                $item['serials'] = $item['serials'] ?? [];
                $productUnits[] = $item;
            }
            $this->merge(['items' => $productUnits]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            foreach (($validator->getData()['items'] ?? []) as $index => $productUnit) {
                $productUnitId = $productUnit['product_unit_id'] ?? null;
                $qty = $productUnit['qty'] ?? null;
                $conversionValue = $productUnit['product_unit_conversion_value'] ?? null;
                $serials = $productUnit['serials'] ?? [];

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

                $serialCount = (string) count(is_array($serials) ? $serials : []);
                if (bccomp($serialCount, $baseQty, 8) !== 0) {
                    $validator->errors()->add('items.'.$index.'.serials', trans('rules.stock_transfer.serial_count_must_match_base_qty'));
                }
            }
        });
    }
}
