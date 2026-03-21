<?php

namespace App\Http\Requests\StockAdjustment;

use App\Helpers\HashidsHelper;
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
            'out_warehouse_id' => ['present', 'nullable', 'integer', 'different:in_warehouse_id', 'required_without:in_warehouse_id', new IsValidWarehouse($this->company_id, false)],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],
            'is_posted' => ['required', 'boolean'],

            'delete_in_product_ids' => ['present', 'array'],
            'delete_in_product_ids.*' => ['required', 'integer', 'distinct', new ExistsForCompany('stock_adjustment_in_products', $this->company_id)],
            'in_products' => ['array', 'required_with:in_warehouse_id'],
            'in_products.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('stock_adjustment_in_products', $this->company_id)],
            'in_products.*.qty' => ['required', 'numeric', 'min:1'],
            'in_products.*.product_unit_id' => ['required', 'integer', 'distinct', new ExistsForCompany('product_units', $this->company_id)],
            'in_products.*.product_unit_conversion_value' => ['required', 'numeric', 'min:1'],
            'in_products.*.product_unit_cogs' => ['required', 'numeric', 'min:0'],
            'in_products.*.remarks' => ['present', 'nullable', 'string', 'max:255'],

            'in_products.*.delete_serial_ids' => ['present', 'array'],
            'in_products.*.delete_serial_ids.*' => ['required', 'integer', 'distinct', new ExistsForCompany('stock_adjustment_in_product_serials', $this->company_id)],
            'in_products.*.serials' => ['present', 'array'],
            'in_products.*.serials.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('stock_adjustment_in_product_serials', $this->company_id)],
            'in_products.*.serials.*.serial' => ['required', 'string', 'max:255'],

            'delete_out_product_ids' => ['present', 'array'],
            'delete_out_product_ids.*' => ['required', 'integer', 'distinct', new ExistsForCompany('stock_adjustment_out_products', $this->company_id)],
            'out_products' => ['array', 'required_with:out_warehouse_id'],
            'out_products.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('stock_adjustment_out_products', $this->company_id)],
            'out_products.*.qty' => ['required', 'numeric', 'min:1'],
            'out_products.*.product_unit_id' => ['required', 'integer', 'distinct', new ExistsForCompany('product_units', $this->company_id)],
            'out_products.*.product_unit_conversion_value' => ['required', 'numeric', 'min:1'],
            'out_products.*.remarks' => ['present', 'nullable', 'string', 'max:255'],

            'out_products.*.delete_serial_ids' => ['present', 'array'],
            'out_products.*.delete_serial_ids.*' => ['required', 'integer', 'distinct', new ExistsForCompany('stock_adjustment_out_product_serials', $this->company_id)],
            'out_products.*.serials' => ['present', 'array'],
            'out_products.*.serials.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('stock_adjustment_out_product_serials', $this->company_id)],
            'out_products.*.serials.*.serial' => ['required', 'string', 'max:255'],
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

        if (is_array($this->input('delete_in_product_ids'))) {
            $deleteInProductIds = [];
            foreach ($this->input('delete_in_product_ids') as $deleteInProductId) {
                $deleteInProductIds[] = HashidsHelper::decodeId($deleteInProductId);
            }
            $this->merge(['delete_in_product_ids' => $deleteInProductIds]);
        }

        if (is_array($this->input('in_products'))) {
            $inProducts = [];
            foreach ($this->input('in_products') as $item) {
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

                $inProducts[] = $item;
            }
            $this->merge(['in_products' => $inProducts]);
        }

        if (is_array($this->input('delete_out_product_ids'))) {
            $deleteOutProductIds = [];
            foreach ($this->input('delete_out_product_ids') as $deleteOutProductId) {
                $deleteOutProductIds[] = HashidsHelper::decodeId($deleteOutProductId);
            }
            $this->merge(['delete_out_product_ids' => $deleteOutProductIds]);
        }

        if (is_array($this->input('out_products'))) {
            $outProducts = [];
            foreach ($this->input('out_products') as $item) {
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

                $outProducts[] = $item;
            }
            $this->merge(['out_products' => $outProducts]);
        }
    }
}
