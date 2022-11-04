<?php

namespace App\Http\Requests;

use App\Models\Warehouse;
use App\Enums\RecordStatus;
use App\Rules\isValidBranch;
use App\Rules\isValidCompany;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Foundation\Http\FormRequest;

class WarehouseRequest extends FormRequest
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

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'list':
                return $user->can('viewAny', Warehouse::class) ? true : false;
            case 'read':
                return $user->can('view', Warehouse::class, $warehouse) ? true : false;
            case 'store':
                return $user->can('create', Warehouse::class) ? true : false;
            case 'update':
                return $user->can('update', Warehouse::class, $warehouse) ? true : false;
            case 'delete':
                return $user->can('delete', Warehouse::class, $warehouse) ? true : false;
            default:
                return false;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $nullableArr = [
            'address' => ['nullable', 'max:255'],
            'city' => ['nullable', 'max:255'],
            'contact' => ['nullable', 'max:255'],
            'remarks' => ['nullable', 'max:255'],
        ];

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'list':
                $rules_list = [
                    'company_id' => ['required', new isValidCompany(), 'bail'],
                    'search' => ['present', 'string'],
                    'paginate' => ['required', 'boolean'],
                    'page' => ['required_if:paginate,true', 'numeric'],
                    'perPage' => ['required_if:paginate,true', 'numeric'],
                    'refresh' => ['nullable', 'boolean'],
                ];

                return $rules_list;
            case 'read':
                $rules_read = [
                ];

                return $rules_read;
            case 'store':
                $rules_store = [
                    'company_id' => ['required', new isValidCompany(), 'bail'],
                    'branch_id' => ['required', new isValidBranch($this->company_id)],
                    'code' => ['required', 'max:255'],
                    'name' => 'required|max:255',
                    'status' => [new Enum(RecordStatus::class)],
                ];

                return array_merge($rules_store, $nullableArr);
            case 'update':
            $rules_update = [
                'company_id' => ['required', new isValidCompany(), 'bail'],
                'branch_id' => ['required', new isValidBranch($this->company_id)],
                'code' => ['required', 'max:255'],
                'name' => 'required|max:255',
                'status' => [new Enum(RecordStatus::class)],
            ];

                return array_merge($rules_update, $nullableArr);
            default:
                return [
                    '' => 'required',
                ];
        }
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
        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'list':
                $this->merge([
                    'company_id' => $this->has('companyId') ? Hashids::decode($this['companyId'])[0] : '',
                    'branch_id' => $this->has('branchId') ? Hashids::decode($this['branchId'])[0] : '',
                    'paginate' => $this->has('paginate') ? filter_var($this->paginate, FILTER_VALIDATE_BOOLEAN) : true,
                ]);
                break;
            case 'read':
                $this->merge([]);
                break;
            case 'store':
            case 'update':
                $this->merge([
                    'company_id' => $this->has('company_id') ? Hashids::decode($this['company_id'])[0] : '',
                    'branch_id' => $this->has('branch_id') ? Hashids::decode($this['branch_id'])[0] : '',
                    'status' => RecordStatus::isValid($this->status) ? RecordStatus::resolveToEnum($this->status)->value : -1,
                ]);
                break;
            default:
                $this->merge([]);
                break;
        }
    }
}
