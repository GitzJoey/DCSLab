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

use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\table;

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

        match (strtolower($this->argument('args'))) {
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
                        $roleId,
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
                $this->error($e->getMessage());
                $this->info('');
                $this->error('Retrying...');
            }
        } while ($invalid);
    }
}
