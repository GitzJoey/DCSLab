<?php

namespace App\Rules;

use App\Services\RoleService;
use Illuminate\Container\Container;
use Illuminate\Contracts\Validation\Rule;

class unchangedRoleName implements Rule
{
    private $id;
    private $roleService;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->id = $id;

        $this->roleService = Container::getInstance()->make(RoleService::class);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $role = $this->roleService->getRoleById($this->id);

        return strcmp($role['name'], $value) == 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('rules.unchanged_role_name');
    }
}
