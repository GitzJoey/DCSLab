<?php

namespace App\Http\Requests\Branch;

use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Models\Branch;
use App\Rules\BranchStoreValidStatus;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class BranchStoreRequest extends FormRequest
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

        return $user->can('create', Branch::class) ? true : false;
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
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'is_main' => ['required', 'boolean'],
            'remarks' => ['nullable', 'string', 'max:255'],
            'status' => ['required', new Enum(RecordStatusEnum::class), 'bail', new BranchStoreValidStatus($this->input('is_main'))],
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
