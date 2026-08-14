<?php

namespace App\Http\Requests\Asset;

use App\Enums\RecordStatusEnum;
use App\Rules\ExistsForCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class AssetUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User $user */
        $user = Auth::user();
        $asset = $this->route('asset');

        return $user->can('update', $asset);
    }

    public function prepareForValidation()
    {
        $asset = $this->route('asset');

        $this->merge([
            'company_id' => $asset?->company_id,
        ]);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer'],
            'asset_category_id' => ['required', 'integer', new ExistsForCompany('asset_categories', $this->company_id)],
            'code' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'asset_unit_id' => ['required', 'integer', new ExistsForCompany('asset_units', $this->company_id)],
            'status' => ['required', 'integer', new Enum(RecordStatusEnum::class)],
            'remarks' => ['present', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return trans('validation_attributes.asset');
    }
}
