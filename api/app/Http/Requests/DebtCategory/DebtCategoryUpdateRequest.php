<?php

namespace App\Http\Requests\DebtCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DebtCategoryUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $debtCategory = $this->route('debt_category');

        return $user->can('update', $debtCategory);
    }

    public function prepareForValidation()
    {
        $debtCategory = $this->route('debt_category');

        $this->merge([
            'company_id' => $debtCategory?->company_id,
        ]);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer'],
            'code' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'sequence' => ['required', 'integer', 'min:0'],
        ];
    }

    public function attributes()
    {
        return trans('validation_attributes.debt_category');
    }
}
