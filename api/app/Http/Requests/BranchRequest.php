<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BranchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
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
                return $user->can('viewAny', Branch::class);
            case 'read':
                return $user->can('view', Branch::class);
            case 'store':
                return $user->can('create', Branch::class);
            case 'update':
                return $user->can('update', Branch::class);
            case 'delete':
                return $user->can('delete', Branch::class);
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
            'view'    => $this->viewRules(),
            'create'  => array_merge($this->createRules(), $this->nullableFields()),
            'update'  => array_merge($this->updateRules(), $this->nullableFields()),
            'delete'  => array_merge($this->deleteRules()),
            default   => $this->defaultRules(),
        };
    }

    public function prepareForValidation()
    {
        match ($this->route()?->getActionMethod()) {
            'viewAny'           => $this->prepareViewAnyData(),
            'create', 'update'   => $this->prepareWriteData(),
            default             => null,
        };
    }

    private function viewAnyRules(): array
    {
        return [
            'company_id' => ['required', new IsValidCompany(), 'bail'],
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

    private function createRules(): array
    {
        return [
            'company_id' => ['required', new IsValidCompany(), 'bail'],
            'code' => ['required', 'max:255'],
            'name' => ['required', 'max:255'],
            'is_main' => ['boolean'],
            'status' => [new Enum(RecordStatus::class)],
        ];
    }

    private function updateRules(): array
    {
        $branch = $this->route('branch');

        return [
            'company_id' => ['required', new IsValidCompany(), 'bail'],
            'code' => ['required', 'max:255'],
            'name' => ['required', 'max:255'],
            'is_main' => ['boolean', new SetBranchToNonMain($branch)],
            'status' => [new Enum(RecordStatus::class)],
        ];
    }

    private function deleteRules(): array
    {
        return [

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

    private function prepareReadAnyData(): void
    {
        $this->merge([
            'search'   => $this->input('search', ''), 
            'paginate' => $this->has('paginate') ? filter_var($this->paginate, FILTER_VALIDATE_BOOLEAN) : true,
        ]);
    }

    private function prepareWriteData(): void
    {

    }
}
