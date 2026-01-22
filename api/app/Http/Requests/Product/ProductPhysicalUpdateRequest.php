<?php

namespace App\Http\Requests\Product;

use App\Enums\ProductTypeEnum;
use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Models\Product;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;

class ProductPhysicalUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $product = $this->route('product');

        return $user->can('update', Product::class, $product);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'code' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', new ExistsForCompany('product_categories', $this->company_id)],
            'brand_id' => ['nullable', 'integer', new ExistsForCompany('brands', $this->company_id)],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'is_taxable' => ['required', 'boolean'],
            'vat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_price_include_vat' => ['required', 'boolean'],
            'is_use_serial_number' => ['required', 'boolean'],
            'is_expirable' => ['required', 'boolean'],
            'remarks' => ['nullable', 'string', 'max:255'],
            'type' => ['required', new Enum(ProductTypeEnum::class)],
            'status' => ['required', new Enum(RecordStatusEnum::class)],

            'delete_product_unit_ids' => ['nullable', 'array'],
            'delete_product_unit_ids.*' => ['required', 'integer', new ExistsForCompany('product_units', $this->company_id)],

            'product_units' => ['required', 'array'],
            'product_units.*.id' => ['nullable', 'integer', new ExistsForCompany('product_units', $this->company_id)],
            'product_units.*.code' => 'required|string',
            'product_units.*.is_manufacturer_sku' => 'required|boolean',
            'product_units.*.unit_id' => ['required', 'integer', new ExistsForCompany('units', $this->company_id), 'distinct'],
            'product_units.*.price' => 'required|numeric|min:0',
            'product_units.*.is_base' => 'required|boolean',
            'product_units.*.conversion_value' => 'required|numeric|min:1|distinct',
            'product_units.*.is_primary_unit' => 'required|boolean',
            'product_units.*.point' => 'required|integer|min:0',
            'product_units.*.remarks' => 'nullable|string',
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.product.company'),
            'code' => trans('validation_attributes.product.code'),
            'category_id' => trans('validation_attributes.product.product_category'),
            'brand_id' => trans('validation_attributes.product.brand'),
            'name' => trans('validation_attributes.product.name'),
            'slug' => trans('validation_attributes.product.slug'),
            'is_taxable' => trans('validation_attributes.product.is_taxable'),
            'vat_rate' => trans('validation_attributes.product.vat_rate'),
            'is_price_include_vat' => trans('validation_attributes.product.is_price_include_vat'),
            'is_use_serial_number' => trans('validation_attributes.product.is_use_serial_number'),
            'is_expirable' => trans('validation_attributes.product.is_expirable'),
            'remarks' => trans('validation_attributes.product.remarks'),
            'type' => trans('validation_attributes.product.product_type'),
            'status' => trans('validation_attributes.product.status'),
        ];
    }

    public function validationData()
    {
        return $this->all();
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'category_id' => $this->filled('category_id') ? HashidsHelper::decodeId($this->category_id) : null,
            'brand_id' => $this->filled('brand_id') ? HashidsHelper::decodeId($this->brand_id) : null,
            'slug' => $this->slug === '_AUTO_' ? '_AUTO_' : Str::slug($this->slug),
            'status' => RecordStatusEnum::isValid($this->status) ? RecordStatusEnum::resolveToEnum($this->status)->value : null,
        ]);

        if ($this->filled('delete_product_unit_ids')) {
            $deleteProductUnitIds = $this->delete_product_unit_ids;
            foreach ($deleteProductUnitIds as $index => $id) {
                $deleteProductUnitIds[$index] = HashidsHelper::decodeId($id);
            }
            $this->merge(['delete_product_unit_ids' => $deleteProductUnitIds]);
        }

        if ($this->filled('product_units')) {
            $productUnits = $this->product_units;
            foreach ($productUnits as $index => $unit) {
                if ($this->filled("product_units.$index.id")) {
                    $productUnits[$index]['id'] = HashidsHelper::decodeId($unit['id']);
                }
                if (isset($unit['unit_id'])) {
                    $productUnits[$index]['unit_id'] = HashidsHelper::decodeId($unit['unit_id']);
                }
            }
            $this->merge(['product_units' => $productUnits]);
        }
    }
}
