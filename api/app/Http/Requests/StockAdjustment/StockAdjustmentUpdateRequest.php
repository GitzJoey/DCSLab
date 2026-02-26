<?php

namespace App\Http\Requests\StockAdjustment;

use App\Helpers\HashidsHelper;
use App\Models\StockAdjustment;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidWarehouse;
use App\Validation\StockAdjustment\StockAdjustmentInProductRules;
use App\Validation\StockAdjustment\StockAdjustmentOutProductRules;
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
        $rules = [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'code' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date_format:Y-m-d H:i:s'],
            'category_id' => ['required', 'integer', new ExistsForCompany('stock_adjustment_categories', $this->company_id)],
            'in_warehouse_id' => ['nullable', 'integer', 'required_without:out_warehouse_id', new IsValidWarehouse($this->company_id, false)],
            'out_warehouse_id' => ['nullable', 'integer', 'different:in_warehouse_id', 'required_without:in_warehouse_id', new IsValidWarehouse($this->company_id, false)],
            'remarks' => ['nullable', 'string', 'max:255'],
            'is_posted' => ['required', 'boolean'],
        ];

        $rules['delete_in_product_ids'] = ['nullable', 'array'];
        $rules['delete_in_product_ids.*'] = ['required', 'integer', new ExistsForCompany('stock_adjustment_in_products', $this->company_id)];

        $rules['in_products'] = ['nullable', 'array'];
        $rules['in_products.*.id'] = ['nullable', 'integer', new ExistsForCompany('stock_adjustment_in_products', $this->company_id)];
        $rules += StockAdjustmentInProductRules::mapToFieldNames($this->company_id ?? 0,
            'in_products.*.qty',
            'in_products.*.product_unit_id',
            'in_products.*.product_unit_conversion_value',
            'in_products.*.product_unit_cogs',
            'in_products.*.remarks',
        );

        $rules['delete_out_product_ids'] = ['nullable', 'array'];
        $rules['delete_out_product_ids.*'] = ['required', 'integer', new ExistsForCompany('stock_adjustment_out_items', $this->company_id)];

        $rules['out_products'] = ['nullable', 'array'];
        $rules['out_products.*.id'] = ['nullable', 'integer', new ExistsForCompany('stock_adjustment_out_items', $this->company_id)];
        $rules += StockAdjustmentOutProductRules::mapToFieldNames($this->company_id ?? 0,
            'out_products.*.qty',
            'out_products.*.product_unit_id',
            'out_products.*.product_unit_conversion_value',
            'out_products.*.remarks',
        );

        return $rules;
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

        if ($this->filled('delete_in_product_ids')) {
            $deleteInProductIds = $this->delete_in_product_ids;
            foreach ($deleteInProductIds as $index => $id) {
                $deleteInProductIds[$index] = HashidsHelper::decodeId($id);
            }
            $this->merge(['delete_in_product_ids' => $deleteInProductIds]);
        }

        if (is_array($this->input('in_products'))) {
            $inProducts = [];
            foreach ($this->input('in_products') as $item) {
                if (isset($item['id'])) {
                    $item['id'] = HashidsHelper::decodeId($item['id']);
                }
                if (isset($item['product_unit_id'])) {
                    $item['product_unit_id'] = HashidsHelper::decodeId($item['product_unit_id']);
                }
                $inProducts[] = $item;
            }
            $this->merge(['in_products' => $inProducts]);
        }

        if ($this->filled('delete_out_product_ids')) {
            $deleteOutProductIds = $this->delete_out_product_ids;
            foreach ($deleteOutProductIds as $index => $id) {
                $deleteOutProductIds[$index] = HashidsHelper::decodeId($id);
            }
            $this->merge(['delete_out_product_ids' => $deleteOutProductIds]);
        }

        if (is_array($this->input('out_products'))) {
            $outProducts = [];
            foreach ($this->input('out_products') as $item) {
                if (isset($item['id'])) {
                    $item['id'] = HashidsHelper::decodeId($item['id']);
                }
                if (isset($item['product_unit_id'])) {
                    $item['product_unit_id'] = HashidsHelper::decodeId($item['product_unit_id']);
                }
                $outProducts[] = $item;
            }
            $this->merge(['out_products' => $outProducts]);
        }
    }
}
