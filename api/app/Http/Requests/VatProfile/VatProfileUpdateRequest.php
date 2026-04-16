<?php

namespace App\Http\Requests\VatProfile;

use App\Helpers\HashidsHelper;
use App\Models\VatProfile;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class VatProfileUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $vatProfile = $this->route('vat_profile');

        return $user->can('update', VatProfile::class, $vatProfile);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'code' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'vat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'vat_base_numerator' => ['required', 'integer', 'min:0'],
            'vat_base_denominator' => ['required', 'integer', 'min:1'],
            'remarks' => ['present', 'nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.vat_profile.company'),
            'code' => trans('validation_attributes.vat_profile.code'),
            'name' => trans('validation_attributes.vat_profile.name'),
            'vat_rate' => trans('validation_attributes.vat_profile.vat_rate'),
            'vat_base_numerator' => trans('validation_attributes.vat_profile.vat_base_numerator'),
            'vat_base_denominator' => trans('validation_attributes.vat_profile.vat_base_denominator'),
            'remarks' => trans('validation_attributes.vat_profile.remarks'),
            'is_active' => trans('validation_attributes.vat_profile.is_active'),
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
        ]);
    }
}
