<?php

namespace App\Http\Requests;

use App\Enums\RecordStatus;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization Error
        // return false;

        if (! Auth::check()) {
            return false;
        }

        /** @var User */
        $authUser = Auth::user();
        $user = $this->route('user');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'index':
                return $authUser->can('viewAny', User::class);
            case 'view':
                return $authUser->can('view', User::class);
            case 'store':
                return $authUser->can('store', User::class);
            case 'update':
                return $authUser->can('update', User::class);
            case 'delete':
                return false;
            default:
                return false;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return match ($this->route()?->getActionMethod()) {
            'viewAny' => $this->viewAnyRules(),
            'view' => $this->viewRules(),
            'create' => array_merge($this->createRules(), $this->nullableFields()),
            'update' => array_merge($this->updateRules(), $this->nullableFields()),
            default => $this->defaultRules(),
        };
    }

    public function prepareForValidation(): void
    {
        match ($this->route()?->getActionMethod()) {
            'viewAny' => $this->prepareViewAnyData(),
            'create', 'update' => $this->prepareWriteData(),
            default => null,
        };
    }

    public function attributes()
    {
        return [
            'name' => trans('request.attributes.user.name'),
            'email' => trans('request.attributes.user.email'),
            'roles' => trans('request.attributes.user.roles'),
            'tax_id' => trans('request.attributes.user.tax_id'),
            'ic_num' => trans('request.attributes.user.ic_num'),
            'country' => trans('request.attributes.user.country'),
            'status' => trans('request.attributes.user.status'),
        ];
    }

    private function viewAnyRules(): array
    {
        // Validation Error
        /*
        $rules_read_any = [
            'search' => ['required'],
            'paginate' => ['required', 'boolean'],
            'page' => ['required_if:paginate,true', 'numeric'],
            'per_page' => ['required_if:paginate,true', 'numeric'],
            'refresh' => ['nullable', 'boolean'],
        ];

        return $rules_read_any;
        */

        $rules_read_any = [
            'search' => ['present', 'string'],
            'paginate' => ['required', 'boolean'],
            'page' => ['required_if:paginate,true', 'numeric'],
            'per_page' => ['required_if:paginate,true', 'numeric'],
            'refresh' => ['nullable', 'boolean'],
        ];

        return $rules_read_any;
    }

    private function viewRules(): array
    {
        return [

        ];
    }

    private function createRules(): array
    {
        return [
            'name' => ['required', 'alpha_num'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'roles' => ['required'],
            'tax_id' => ['required'],
            'ic_num' => ['required'],
            'status' => [new Enum(RecordStatus::class)],
            'country' => ['required'],
        ];
    }

    private function updateRules(): array
    {
        $id = $this->route('user')->id;
        $rules = [
            'name' => ['required', 'alpha_num'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'roles' => ['required'],
            'tax_id' => ['required'],
            'ic_num' => ['required'],
            'status' => [new Enum(RecordStatus::class)],
            'country' => ['required'],

            'api_token' => ['nullable', 'boolean'],
            'reset_password' => ['nullable', 'boolean'],
            'reset_2fa' => ['nullable', 'boolean'],
        ];

        return $rules;
    }

    private function nullableFields(): array
    {
        return [
            'first_name' => 'nullable',
            'last_name' => 'nullable',
            'address' => 'nullable',
            'city' => 'nullable',
            'postal_code' => 'nullable',
            'img_path' => 'nullable',
            'remarks' => 'nullable',
            'theme' => 'nullable',
            'date_format' => 'nullable',
            'time_format' => 'nullable',
        ];
    }

    private function defaultRules(): array
    {
        return [
            '' => 'required',
        ];
    }

    private function prepareViewAnyData(): void
    {
        $this->merge([
            'search' => $this->input('search', ''),
            'paginate' => $this->has('paginate') ? filter_var($this->paginate, FILTER_VALIDATE_BOOLEAN) : true,
        ]);
    }

    private function prepareWriteData(): void
    {
        if ($this->has('roles')) {
            $this->merge([
                'roles' => collect($this->input('roles'))->pluck('id')->all(),
            ]);
        }

        if ($this->has('status')) {
            $this->merge([
                'status' => RecordStatus::isValid($this->status) ? $this->status : -1,
            ]);
        }
    }
}
