<?php

namespace App\Http\Requests\NonCapitalWithdrawalCategory;

use App\Helpers\HashidsHelper;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class NonCapitalWithdrawalCategoryUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $nonCapitalWithdrawalCategory = $this->route('non_capital_withdrawal_category');

        return $user->can('update', $nonCapitalWithdrawalCategory);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'code' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.non_capital_withdrawal_category.company'),
            'code' => trans('validation_attributes.non_capital_withdrawal_category.code'),
            'name' => trans('validation_attributes.non_capital_withdrawal_category.name'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
        ]);
    }
}
