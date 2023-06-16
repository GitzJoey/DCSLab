<?php

namespace App\Http\Requests;

use App\Enums\UnitCategory;
use App\Models\Unit;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Vinkla\Hashids\Facades\Hashids;

class UnitRequest extends FormRequest
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
        $unit = $this->route('unit');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                return $user->can('viewAny', Unit::class) ? true : false;
            case 'read':
                return $user->can('view', Unit::class, $unit) ? true : false;
            case 'store':
                return $user->can('create', Unit::class) ? true : false;
            case 'update':
                return $user->can('update', Unit::class, $unit) ? true : false;
            case 'delete':
                return $user->can('delete', Unit::class, $unit) ? true : false;
            default:
                return false;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $nullableArr = [
            'description' => ['nullable', 'max:255'],
        ];

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'readAny':
                $rules_read_any = [
                    'company_id' => ['required', new IsValidCompany(), 'bail'],
                    'category' => ['exclude_if:category,-1', new Enum(UnitCategory::class)],
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
                    'name' => ['required', 'min:2', 'max:255'],
                    'category' => [new Enum(UnitCategory::class)],
                ];

                return array_merge($rules_store, $nullableArr);
            case 'update':
                $rules_update = [
                    'company_id' => ['required', new IsValidCompany(), 'bail'],
                    'code' => ['required', 'max:255'],
                    'name' => ['required', 'min:2', 'max:255'],
                    'category' => [new Enum(UnitCategory::class)],
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
            'company_id' => trans('validation_attributes.unit.company'),
            'code' => trans('validation_attributes.unit.code'),
            'name' => trans('validation_attributes.unit.name'),
            'description' => trans('validation_attributes.unit.description'),
            'category' => trans('validation_attributes.unit.category'),
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
                    'category' => UnitCategory::isValid($this->category) ? UnitCategory::resolveToEnum($this->category)->value : -1,
                ]);
                break;
            case 'read':
                $this->merge([]);
                break;
            case 'store':
            case 'update':
                $this->merge([
                    'company_id' => $this->has('company_id') ? Hashids::decode($this['company_id'])[0] : '',
                    'category' => UnitCategory::isValid($this->category) ? UnitCategory::resolveToEnum($this->category)->value : -1,
                ]);
                break;
            default:
                $this->merge([]);
                break;
        }
    }
}
