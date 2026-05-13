<?php

namespace App\Http\Requests\Asset;

use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Models\Asset;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class AssetStoreRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User $user */
        $user = Auth::user();

        return $user->can('create', Asset::class);
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'asset_category_id' => $this->filled('asset_category_id') ? HashidsHelper::decodeId($this->asset_category_id) : null,
            'asset_unit_id' => $this->filled('asset_unit_id') ? HashidsHelper::decodeId($this->asset_unit_id) : null,
        ]);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
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
