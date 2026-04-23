<?php

namespace App\Http\Requests\LiabilityCreditor;

use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class LiabilityCreditorUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $liabilityCreditor = $this->route('liability_creditor');

        return $user->can('update', $liabilityCreditor);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'code' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.liability_creditor.company'),
            'code' => trans('validation_attributes.liability_creditor.code'),
            'name' => trans('validation_attributes.liability_creditor.name'),
            'remarks' => trans('validation_attributes.liability_creditor.remarks'),
        ];
    }

    public function validationData()
    {
        return $this->all();
    }

    public function prepareForValidation()
    {
        $liabilityCreditor = $this->route('liability_creditor');

        $this->merge([
            'company_id' => $liabilityCreditor?->company_id,
        ]);
    }
}
