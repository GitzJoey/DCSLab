<?php

namespace App\Http\Requests;

use App\Enums\RecordStatus;
use App\Models\Warehouse;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Vinkla\Hashids\Facades\Hashids;

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
            case 'readAny':
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
            case 'readAny':
                $rules_read_any = [
                    'company_id' => ['required', new IsValidCompany(), 'bail'],
                    'search' => ['present', 'string'],
                    'paginate' => ['required', 'boolean'],
                    'page' => ['required_if:paginate,true', 'numeric'],
                    'per_page' => ['required_if:paginate,true', 'numeric'],
                    'refresh' => ['nullable', 'boolean'],
                ];

                return $rules_read_any;
            case 'read':
                $rules_read = [
                ];

                return $rules_read;
            case 'store':
                $rules_store = [
                    'company_id' => ['required', new IsValidCompany(), 'bail'],
                    'branch_id' => ['required'],
                    'code' => ['required', 'max:255'],
                    'name' => 'required|max:255',
                    'status' => [new Enum(RecordStatus::class)],
                ];

                return array_merge($rules_store, $nullableArr);
            case 'update':
                $rules_update = [
                    'company_id' => ['required', new IsValidCompany(), 'bail'],
                    'branch_id' => ['required'],
                    'code' => ['required', 'max:255'],
                    'name' => 'required|max:255',
                    'status' => [new Enum(RecordStatus::class)],
                ];

                return array_merge($rules_update, $nullableArr);

            case 'delete':
                $rules_delete = [

                ];

                return $rules_delete;
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
            case 'readAny':
                $this->merge([
                    'company_id' => $this->has('company_id') ? Hashids::decode($this['company_id'])[0] : '',
                    'branch_id' => $this->has('branch_id') ? Hashids::decode($this['branch_id'])[0] : '',
                    'search' => $this->has('search') && ! is_null($this->search) ? $this->search : '',
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
