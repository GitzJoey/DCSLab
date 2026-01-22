<?php

namespace App\Http\Requests\Company;

use App\Enums\RecordStatusEnum;
use App\Rules\CompanyUpdateValidDefault;
use App\Rules\CompanyUpdateValidStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class CompanyUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\Models\User */
        $user = Auth::user();

        return $user->can('update', $this->route('company'));
    }

    public function rules()
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        return [
            'code' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'default' => ['required', 'boolean', new CompanyUpdateValidDefault($user)],
            'status' => ['required', new Enum(RecordStatusEnum::class), new CompanyUpdateValidStatus($this->input('default'))],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.company.company'),
            'code' => trans('validation_attributes.company.code'),
            'name' => trans('validation_attributes.company.name'),
            'address' => trans('validation_attributes.company.address'),
            'default' => trans('validation_attributes.company.default'),
            'status' => trans('validation_attributes.company.status'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'status' => RecordStatusEnum::isValid($this->status) ? RecordStatusEnum::resolveToEnum($this->status)->value : null,
        ]);
    }
}
