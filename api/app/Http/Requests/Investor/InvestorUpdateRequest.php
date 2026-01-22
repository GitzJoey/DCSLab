<?php

namespace App\Http\Requests\Investor;

use App\Helpers\HashidsHelper;
use App\Models\Investor;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class InvestorUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $investor = $this->route('investor');

        return $user->can('update', Investor::class, $investor);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'code' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.investor.company'),
            'code' => trans('validation_attributes.investor.code'),
            'name' => trans('validation_attributes.investor.name'),
            'remarks' => trans('validation_attributes.investor.remarks'),
        ];
    }

    public function validationData()
    {
        return $this->all();
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'remarks' => $this->has('remarks') ? $this['remarks'] : null,
        ]);
    }
}
