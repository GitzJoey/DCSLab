<?php

namespace App\Http\Requests\AssetUnit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AssetUnitUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User $user */
        $user = Auth::user();
        $assetUnit = $this->route('asset_unit');

        return $user->can('update', $assetUnit);
    }

    public function prepareForValidation()
    {
        $assetUnit = $this->route('asset_unit');

        $this->merge([
            'company_id' => $assetUnit?->company_id,
        ]);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer'],
            'code' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['present', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return trans('validation_attributes.asset_unit');
    }
}
