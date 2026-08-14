<?php

namespace App\Http\Requests\DebtCreditor;

use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DebtCreditorUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $debtCreditor = $this->route('debt_creditor');

        return $user->can('update', $debtCreditor);
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
            'company_id' => trans('validation_attributes.debt_creditor.company'),
            'code' => trans('validation_attributes.debt_creditor.code'),
            'name' => trans('validation_attributes.debt_creditor.name'),
            'remarks' => trans('validation_attributes.debt_creditor.remarks'),
        ];
    }

    public function validationData()
    {
        return $this->all();
    }

    public function prepareForValidation()
    {
        $debtCreditor = $this->route('debt_creditor');

        $this->merge([
            'company_id' => $debtCreditor?->company_id,
        ]);
    }
}
