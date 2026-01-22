<?php

namespace App\Http\Requests\Product;

use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Models\Product;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;

class ProductServiceStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', Product::class);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'code' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', new ExistsForCompany('product_categories', $this->company_id)],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'is_taxable' => ['required', 'boolean'],
            'vat_rate' => ['required', 'numeric'],
            'is_price_include_vat' => ['required', 'boolean'],
            'remarks' => ['nullable', 'string', 'max:255'],
            'status' => ['required', new Enum(RecordStatusEnum::class)],
            'unit_id' => ['required', 'integer', new ExistsForCompany('units', $this->company_id)],
            'price' => ['required', 'numeric', 'min:0'],
            'point' => 'required|integer|min:0',
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.product.company'),
            'code' => trans('validation_attributes.product.code'),
            'category_id' => trans('validation_attributes.product.product_category'),
            'name' => trans('validation_attributes.product.name'),
            'slug' => trans('validation_attributes.product.slug'),
            'is_taxable' => trans('validation_attributes.product.is_taxable'),
            'vat_rate' => trans('validation_attributes.product.vat_rate'),
            'is_price_include_vat' => trans('validation_attributes.product.is_price_include_vat'),
            'remarks' => trans('validation_attributes.product.remarks'),
            'status' => trans('validation_attributes.product.status'),
        ];
    }

    public function validationData()
    {
        return $this->all();
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'category_id' => $this->filled('category_id') ? HashidsHelper::decodeId($this->category_id) : null,
            'slug' => $this->slug === '_AUTO_' ? '_AUTO_' : Str::slug($this->slug),
            'status' => RecordStatusEnum::isValid($this->status) ? RecordStatusEnum::resolveToEnum($this->status)->value : null,
            'unit_id' => $this->filled('unit_id') ? HashidsHelper::decodeId($this->unit_id) : null,
        ]);
    }
}
