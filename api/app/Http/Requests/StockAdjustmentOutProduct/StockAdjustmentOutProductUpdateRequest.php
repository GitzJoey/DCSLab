<?php

namespace App\Http\Requests\StockAdjustmentOutProduct;

use App\Helpers\HashidsHelper;
use App\Models\StockAdjustmentOutProduct;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentOutProductUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $resource = $this->route('stock_adjustment_out_product');

        return $user->can('update', StockAdjustmentOutProduct::class, $resource) ? true : false;
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['required', 'integer', new IsValidBranch($this->company_id, true)],
            'stock_adjustment_id' => ['required', 'integer', new ExistsForCompany('stock_adjustments', $this->company_id)],
            'qty' => ['required', 'numeric', 'min:1'],
            'product_unit_id' => ['required', 'integer', new ExistsForCompany('product_units', $this->company_id)],
            'product_unit_conversion_value' => ['required', 'numeric', 'min:1'],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],

            'delete_serial_ids' => ['present', 'nullable', 'array'],
            'delete_serial_ids.*' => ['required', 'integer', 'distinct', new ExistsForCompany('stock_adjustment_out_product_serials', $this->company_id)],
            'serials' => ['present', 'nullable', 'array'],
            'serials.*.id' => ['present', 'nullable', 'integer', new ExistsForCompany('stock_adjustment_out_product_serials', $this->company_id)],
            'serials.*.serial' => ['required', 'distinct', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.stock_adjustment_out_product.company_id'),
            'branch_id' => trans('validation_attributes.stock_adjustment_out_product.branch_id'),
            'stock_adjustment_id' => trans('validation_attributes.stock_adjustment_out_product.stock_adjustment_id'),
            'qty' => trans('validation_attributes.stock_adjustment_out_product.qty'),
            'product_unit_id' => trans('validation_attributes.stock_adjustment_out_product.product_unit_id'),
            'product_unit_conversion_value' => trans('validation_attributes.stock_adjustment_out_product.product_unit_conversion_value'),
            'remarks' => trans('validation_attributes.stock_adjustment_out_product.remarks'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'stock_adjustment_id' => $this->filled('stock_adjustment_id') ? HashidsHelper::decodeId($this->stock_adjustment_id) : null,
            'product_unit_id' => $this->filled('product_unit_id') ? HashidsHelper::decodeId($this->product_unit_id) : null,
        ]);

        if ($this->filled('delete_serial_ids')) {
            $deleteSerialIds = $this->delete_serial_ids;
            foreach ($deleteSerialIds as $index => $id) {
                $deleteSerialIds[$index] = HashidsHelper::decodeId($id);
            }
            $this->merge(['delete_serial_ids' => $deleteSerialIds]);
        }

        if (is_array($this->input('serials'))) {
            $serials = [];
            foreach ($this->input('serials') as $item) {
                if (isset($item['id'])) {
                    $item['id'] = HashidsHelper::decodeId($item['id']);
                }
                $serials[] = $item;
            }
            $this->merge(['serials' => $serials]);
        }
    }
}
