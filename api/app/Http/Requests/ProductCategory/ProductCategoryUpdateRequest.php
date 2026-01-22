<?php

namespace App\Http\Requests\ProductCategory;

use App\Enums\ProductCategoryTypeEnum;
use App\Helpers\HashidsHelper;
use App\Models\ProductCategory;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class ProductCategoryUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $productCategory = $this->route('product_category');

        return $user->can('update', ProductCategory::class, $productCategory) ? true : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'code' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', new Enum(ProductCategoryTypeEnum::class)],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.product_category.company'),
            'code' => trans('validation_attributes.product_category.code'),
            'name' => trans('validation_attributes.product_category.name'),
            'type' => trans('validation_attributes.product_category.type'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'type' => ProductCategoryTypeEnum::isValid($this->type) ? ProductCategoryTypeEnum::resolveToEnum($this->type)->value : null,
        ]);
    }
}
