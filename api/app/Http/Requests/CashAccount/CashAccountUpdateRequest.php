<?php

namespace App\Http\Requests\CashAccount;

use App\Helpers\HashidsHelper;
use App\Models\CashAccount;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CashAccountUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $cashAccount = $this->route('cash_account');

        return $user->can('update', CashAccount::class, $cashAccount) ? true : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'code' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'is_bank' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.cash_account.company'),
            'code' => trans('validation_attributes.cash_account.code'),
            'name' => trans('validation_attributes.cash_account.name'),
            'is_bank' => trans('validation_attributes.cash_account.is_bank'),
            'is_active' => trans('validation_attributes.cash_account.is_active'),
            'remarks' => trans('validation_attributes.cash_account.remarks'),
        ];
    }

    public function prepareForValidation()
    {
        if ($this->has('company_id')) {
            $this->merge([
                'company_id' => HashidsHelper::decodeId($this->company_id),
            ]);
        }
    }
}
