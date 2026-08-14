<?php

namespace App\Http\Requests\Branch;

use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Rules\BranchUpdateValidCode;
use App\Rules\BranchUpdateValidIsMain;
use App\Rules\BranchUpdateValidName;
use App\Rules\BranchUpdateValidStatus;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class BranchUpdateRequest extends FormRequest
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

        return $user->can('update', $this->route('branch')) ? true : false;
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
            'code' => ['required', 'string', 'max:255', new BranchUpdateValidCode($this->input('company_id'), $this->route('branch'))],
            'name' => ['required', 'string', 'max:255', new BranchUpdateValidName($this->input('company_id'), $this->route('branch'))],
            'address' => ['present', 'nullable', 'string', 'max:255'],
            'city' => ['present', 'nullable', 'string', 'max:255'],
            'contact' => ['present', 'nullable', 'string', 'max:255'],
            'is_main' => ['required', 'boolean', 'bail', new BranchUpdateValidIsMain($this->route('branch'))],
            'remarks' => ['present', 'nullable', 'string', 'max:255'],
            'status' => ['required', new Enum(RecordStatusEnum::class), 'bail', new BranchUpdateValidStatus($this->input('is_main'))],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.branch.company'),
            'code' => trans('validation_attributes.branch.code'),
            'name' => trans('validation_attributes.branch.name'),
            'address' => trans('validation_attributes.branch.address'),
            'city' => trans('validation_attributes.branch.city'),
            'contact' => trans('validation_attributes.branch.contact'),
            'is_main' => trans('validation_attributes.branch.is_main'),
            'remarks' => trans('validation_attributes.branch.remarks'),
            'status' => trans('validation_attributes.branch.status'),
        ];
    }

    public function validationData()
    {
        $additionalArray = [];

        return array_merge($this->all(), $additionalArray);
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'status' => RecordStatusEnum::isValid($this->status) ? RecordStatusEnum::resolveToEnum($this->status)->value : null,
        ]);
    }
}
