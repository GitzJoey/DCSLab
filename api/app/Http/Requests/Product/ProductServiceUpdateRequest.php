<?php

namespace App\Http\Requests\Product;

use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Models\Product;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class ProductServiceUpdateRequest extends FormRequest
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
            'default_vat_profile_id' => ['present', 'nullable', 'integer', new ExistsForCompany('vat_profiles', $this->company_id)],
            'name' => ['required', 'string', 'max:255'],
            'is_price_include_vat' => ['required', 'boolean'],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],
            'status' => ['required', new Enum(RecordStatusEnum::class)],
            'unit_id' => ['required', 'integer', new ExistsForCompany('units', $this->company_id)],
            'price' => ['required', 'numeric', 'min:0'],
            'point' => 'required|integer|min:0',

            'delete_image_ids' => 'nullable|array',
            'delete_image_ids.*' => [
                'required',
                'integer',
                Rule::exists('product_images', 'id')->where(function ($query) {
                    $product = $this->route('product');
                    if ($product) {
                        $query->where('product_id', $product->id);
                    }
                }),
            ],

            'image_hashes' => ['nullable', 'array'],
            'image_hashes.*.hash' => ['required', 'string', Rule::exists('product_images', 'hash')],
            'image_hashes.*.is_main' => 'required|boolean',
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.product.company'),
            'code' => trans('validation_attributes.product.code'),
            'category_id' => trans('validation_attributes.product.product_category'),
            'default_vat_profile_id' => trans('validation_attributes.product.default_vat_profile'),
            'name' => trans('validation_attributes.product.name'),
            'is_price_include_vat' => trans('validation_attributes.product.is_price_include_vat'),
            'remarks' => trans('validation_attributes.product.remarks'),
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
            'default_vat_profile_id' => $this->filled('default_vat_profile_id') ? HashidsHelper::decodeId($this->default_vat_profile_id) : null,
            'status' => RecordStatusEnum::isValid($this->status) ? RecordStatusEnum::resolveToEnum($this->status)->value : null,
            'unit_id' => $this->filled('unit_id') ? HashidsHelper::decodeId($this->unit_id) : null,
        ]);

        if ($this->filled('delete_image_ids')) {
            $deleteImageIds = $this->delete_image_ids;
            foreach ($deleteImageIds as $index => $id) {
                $deleteImageIds[$index] = HashidsHelper::decodeId($id);
            }
            $this->merge(['delete_image_ids' => $deleteImageIds]);
        }
    }
}
