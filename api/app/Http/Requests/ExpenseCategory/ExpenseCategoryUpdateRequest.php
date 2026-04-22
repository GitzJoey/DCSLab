<?php

namespace App\Http\Requests\ExpenseCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ExpenseCategoryUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $expenseCategory = $this->route('expense_category');

        return $user->can('update', $expenseCategory);
    }

    public function prepareForValidation()
    {
        $expenseCategory = $this->route('expense_category');

        $this->merge([
            'company_id' => $expenseCategory?->company_id,
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
        return trans('validation_attributes.expense_category');
    }
}
