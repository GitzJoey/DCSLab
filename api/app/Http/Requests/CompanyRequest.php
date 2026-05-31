<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        $user = Auth::user();

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'viewAny':
                return $user->can('viewAny', Company::class);
            case 'view':
                return $user->can('view', Company::class);
            case 'create':
                return $user->can('create', Company::class);
            case 'update':
                return $user->can('update', Company::class);
            case 'delete':
                return $user->can('delete', Company::class);
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
        $company = $this->route('company');

        return match ($this->route()?->getActionMethod()) {
            'viewAny' => $this->viewAnyRules(),
            'view' => $this->viewRules(),
            'store' => array_merge($this->storeRules($company), $this->nullableFields()),
            'update' => array_merge($this->updateRules($company), $this->nullableFields()),
            'destroy' => array_merge($this->destroyRules($company)),
            default => $this->defaultRules(),
        };
    }

    public function prepareForValidation()
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
            'company_id' => trans('request.attributes.company.company'),
            'code' => trans('request.attributes.company.code'),
            'name' => trans('request.attributes.company.name'),
            'address' => trans('request.attributes.company.address'),
            'default' => trans('request.attributes.company.default'),
            'status' => trans('request.attributes.company.status'),
        ];
    }

    private function viewAnyRules(): array
    {
        return [
            'search' => ['present', 'string'],
            'paginate' => ['required', 'boolean'],
            'page' => ['required_if:paginate,true', 'numeric'],
            'per_page' => ['required_if:paginate,true', 'numeric'],
            'refresh' => ['nullable', 'boolean'],
        ];
    }

    private function viewRules(): array
    {
        return [

        ];
    }

    private function storeRules(Company $company): array
    {
        return [
            /* Test Validation Error For Code */
            // 'code' => ['required', 'max:1', 'alpha'],
            // 'name' => ['required', 'max:1'],
            /* Test Validation Error For Code */
            'code' => ['required', 'max:255'],
            'name' => ['required', 'max:255'],
            'default' => ['required', 'boolean'],
            'status' => [new Enum(RecordStatus::class), new DeactivateDefaultCompany($company)],
        ];
    }

    private function updateRules(Company $company): array
    {
        return [
            'code' => ['required', 'max:255'],
            'name' => ['required', 'max:255'],
            'default' => ['required', 'boolean', new SetCompanyToNonDefault($user)],
            'status' => [new Enum(RecordStatus::class), new DeactivateDefaultCompany($company)],
        ];
    }

    private function destroyRules(Company $company): array
    {
        return [
            '' => [new DeactivateDefaultCompany($company)],
        ];
    }

    private function defaultRules(): array
    {
        return [
            '' => 'required',
        ];
    }

    private function nullableFields(): array
    {
        return [
        ];
    }

    private function prepareViewAnyData(): void
    {
        $this->merge([
            'search' => $this->input('search', ''),
            'paginate' => $this->has('paginate') ? filter_var($this->paginate, FILTER_VALIDATE_BOOLEAN) : true,
        ]);
    }

    private function prepareWriteData(): void {}
}
