<?php

namespace App\Console\Commands;

use App\Actions\Role\RoleActions;
use App\Actions\User\UserActions;
use App\Enums\RecordStatus;
use App\Enums\UserRole;
use Exception;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\table;
use function Laravel\Prompts\text;

#[Signature('app:user {args=default}')]
#[Description('User Management')]
class AppUser extends Command
{
    public function handle()
    {
        $check = $this->preCheck();
        if ($check !== null) {
            return $check;
        }

        return match (strtolower($this->argument('args'))) {
            'create' => $this->createUser(),

            'changerole',
            'changeroles',
            'changeuserrole',
            'changeuserroles' => $this->changeUserRoles(),

            default => $this->components->error('Invalid action provided.'),
        };
    }

    private function preCheck()
    {
        if (! Schema::hasTable('users')) {
            $this->components->error('Users table not found.');

            return Command::FAILURE;
        }

        return null;
    }

    private function createUser()
    {
        $this->components->info('Creating User Account...');

        $userName = 'GitzJoey';
        $userEmail = 'gitzjoey@yahoo.com';
        $userPassword = 'thepassword';

        $invalid = true;

        $userActions = new UserActions;
        $roleActions = new RoleActions;

        do {
            $userType = select(
                'Select Role',
                UserRole::values(),
                default: UserRole::USER->value
            );
            $userName = text('Name', $userName, $userName, true, null, 'Name is required.', null);
            $userEmail = text('Email', $userEmail, $userEmail, true, null, 'Email is required.', null);
            $userPassword = password('Password', $userPassword, true, null, 'Password is required.', null);

            $role = $roleActions->readBy('NAME', $userType);

            if (is_null($role)) {
                $this->components->error('Role not found.');

                return Command::FAILURE;
            }

            $roleId = $role->id;

            $profile = [
                'first_name' => $userName,
                'tax_id' => 0,
                'ic_num' => 0,
                'country' => 'Singapore',
                'status' => RecordStatus::ACTIVE,
            ];

            $user = [
                'name' => $userName,
                'email' => $userEmail,
                'password' => $userPassword,
            ];

            $confirmed = $this->components->confirm("Everything's OK? Do you wish to continue?");

            try {
                if (! $confirmed) {
                    $this->components->error('Aborted');

                    $invalid = false;
                } else {
                    $user = $userActions->create(
                        $user,
                        [$roleId],
                        $profile
                    );

                    $this->components->info('Account Created.');
                    table(
                        headers: ['Name', 'Email', 'Role'],
                        rows: [[$user->name, $user->email, $userType]]
                    );

                    $invalid = false;
                }
            } catch (Exception $e) {
                $this->components->error($e->getMessage());
                $this->components->info('Retrying...');
            }
        } while ($invalid);

        return Command::SUCCESS;
    }

    private function changeUserRoles()
    {
        $userActions = new UserActions;
        $roleActions = new RoleActions;

        $email = text(
            label: 'Enter Email',
            placeholder: 'gitzjoey@yahoo.com',
            required: true,
            validate: function (string $value) use ($userActions) {
                if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return 'Please enter a valid email address.';
                }
                if (! $userActions->readby('EMAIL', $value)) {
                    return 'No user found with this email address.';
                }

                return null;
            }
        );

        $usr = $userActions->readby('EMAIL', $email);

        $currentRoles = $usr->roles()->get(['id', 'display_name']);

        info("Target User Found: {$usr->name}");
        table(
            headers: ['ID', 'Current Assigned Roles'],
            rows: $currentRoles->isEmpty()
                ? [['-', '[No Roles Assigned]']]
                : $currentRoles->map(fn ($r) => [$r->id, $r->display_name])->toArray()
        );

        $allRoles = $roleActions->readAny();

        $roleOptions = $allRoles->pluck('display_name', 'id')->toArray();

        $currentlyAssignedIds = $currentRoles->pluck('id')->toArray();

        $selectedRoleIds = multiselect(
            label: 'Select Roles',
            options: $roleOptions,
            default: $currentlyAssignedIds,
            required: false,
        );

        $confirmed = confirm(
            label: "Are You Sure You Want To Update Roles For {$usr->name}?",
            default: true
        );

        if (! $confirmed) {
            error('Operation Aborted By User.');

            return Command::SUCCESS;
        }

        $usr->roles()->sync($selectedRoleIds);

        $this->components->info('User Roles Successfully Updated!');

        $freshRoles = $usr->fresh()->roles()->get(['id', 'display_name']);

        table(
            headers: ['ID', 'Updated Assigned Roles'],
            rows: $freshRoles->isEmpty()
                ? [['-', '[No Roles Assigned]']]
                : $freshRoles->map(fn ($r) => [$r->id, $r->display_name])->toArray()
        );

        return Command::SUCCESS;
    }
}
