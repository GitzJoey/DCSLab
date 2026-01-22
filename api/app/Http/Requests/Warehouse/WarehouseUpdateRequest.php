<?php

namespace App\Http\Requests\Warehouse;

use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Models\Warehouse;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\WarehouseUpdateValidStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class WarehouseUpdateRequest extends FormRequest
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
        $warehouse = $this->route('warehouse');

        return $user->can('update', Warehouse::class, $warehouse) ? true : false;
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
            'branch_id' => ['required', 'integer', 'bail', new IsValidBranch($this->input('company_id'), true)],
            'code' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:255'],
            'status' => ['required', new Enum(RecordStatusEnum::class), new WarehouseUpdateValidStatus($this->input('default'))],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.warehouse.company'),
            'branch_id' => trans('validation_attributes.warehouse.branch'),
            'code' => trans('validation_attributes.warehouse.code'),
            'name' => trans('validation_attributes.warehouse.name'),
            'address' => trans('validation_attributes.warehouse.address'),
            'city' => trans('validation_attributes.warehouse.city'),
            'contact' => trans('validation_attributes.warehouse.contact'),
            'remarks' => trans('validation_attributes.warehouse.remarks'),
            'status' => trans('validation_attributes.warehouse.status'),
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
            'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
            'status' => RecordStatusEnum::isValid($this->status) ? RecordStatusEnum::resolveToEnum($this->status)->value : null,
        ]);
    }
}
