<?php

namespace App\Http\Requests\AssetCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AssetCategoryUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $assetCategory = $this->route('asset_category');

        return $user->can('update', $assetCategory);
    }

    public function prepareForValidation()
    {
        $assetCategory = $this->route('asset_category');

        $this->merge([
            'company_id' => $assetCategory?->company_id,
        ]);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer'],
            'code' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'estimated_useful_life_months' => ['required', 'integer', 'min:1'],
            'remarks' => ['present', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return trans('validation_attributes.asset_category');
    }
}
