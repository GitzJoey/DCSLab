<?php

namespace App\Http\Requests;

use App\Enums\RecordStatus;
use App\Models\Branch;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Vinkla\Hashids\Facades\Hashids;

class BranchRequest extends FormRequest
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
        $branch = $this->route('branch');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                return $user->can('viewAny', Branch::class) ? true : false;
            case 'read':
                return $user->can('view', Branch::class, $branch) ? true : false;
            case 'store':
                return $user->can('create', Branch::class) ? true : false;
            case 'update':
                return $user->can('update', Branch::class, $branch) ? true : false;
            case 'delete':
                return $user->can('delete', Branch::class, $branch) ? true : false;
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
                    'code' => ['required', 'max:255'],
                    'name' => ['required', 'max:255'],
                    'is_main' => ['boolean'],
                    'status' => [new Enum(RecordStatus::class)],
                ];

                return array_merge($rules_store, $nullableArr);
            case 'update':
                $rules_update = [
                    'company_id' => ['required', new IsValidCompany(), 'bail'],
                    'code' => ['required', 'max:255'],
                    'name' => ['required', 'max:255'],
                    'is_main' => ['boolean'],
                    'status' => [new Enum(RecordStatus::class)],
                ];

                return array_merge($rules_update, $nullableArr);
            case 'delete':
                $rules_delete = [

                ];

                return $rules_delete;
            default:
                return [
                    '' => ['required'],
                ];
        }
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
        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                $this->merge([
                    'company_id' => $this->has('company_id') ? Hashids::decode($this['company_id'])[0] : '',
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
                    'is_main' => $this->has('is_main') ? filter_var($this->is_main, FILTER_VALIDATE_BOOLEAN) : false,
                    'status' => RecordStatus::isValid($this->status) ? RecordStatus::resolveToEnum($this->status)->value : -1,
                ]);
                break;
            default:
                $this->merge([]);
        }
    }
}
