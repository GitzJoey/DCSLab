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
use Laravel\Prompts\Prompt\{
    text,
    input,
    select,
    secret,
    confirm,
};

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
                'Account Type',
                [
                    UserRole::ADMIN->value => 'Admin',
                    UserRole::USER->value => 'User',
                ],
                default: UserRole::USER->value
            );
            $userName = text('Name', $userName);
            $userEmail = text('Email', $userEmail);
            $userPassword = secret('Password', $userPassword);

            $rolesId = [$roleActions->readBy('NAME', $userType)->id];

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
                    $userActions->create(
                        $user,
                        $rolesId,
                        $profile
                    );

                    $this->info('Creating Account...');
                    $this->info('Name: '.$userName);
                    $this->info('Email: '.$userEmail);
                    $this->info('Password: '.'***********');
                    $this->info('Account Type: '.$userType);

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
