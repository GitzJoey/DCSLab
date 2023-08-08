<?php

namespace App\Console\Commands;

use App\Actions\Role\RoleActions;
use App\Actions\User\UserActions;
use App\Enums\RecordStatus;
use App\Enums\UserRoles;
use Exception;
use Illuminate\Console\Command;

class AppUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:user {args?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'User Management';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        switch (strtolower($this->argument('args'))) {
            case 'create':
                $this->createUser();
                break;
            case 'changerole':
            case 'changeroles':
            case 'changeuserrole':
            case 'changeuserroles':
                $this->changeUserRoles();
                break;
            default:
                break;
        }

        return Command::SUCCESS;
    }

    private function createUser()
    {
        $this->info('Creating User Account...');

        $userName = 'GitzJoey';
        $userEmail = 'gitzjoey@yahoo.com';
        $userPassword = 'thepassword';

        $invalid = true;

        $userActions = new UserActions();
        $roleActions = new RoleActions();

        do {
            $userType = $this->choice('Select Role: ', [
                ucfirst(UserRoles::DEVELOPER->value),
                ucfirst(UserRoles::ADMINISTRATOR->value),
                ucfirst(UserRoles::USER->value),
            ]);
            $userName = $this->ask('Name', $userName);
            $userEmail = $this->ask('Email', $userEmail);
            $userPassword = $this->secret('Password', $userPassword);

            $rolesId = [$roleActions->readBy('NAME', $userType)->id];

            $profile = [
                'first_name' => $userName,
                'country' => 'Singapore',
                'status' => RecordStatus::ACTIVE,
            ];

            $user = [
                'name' => $userName,
                'email' => $userEmail,
                'password' => $userPassword,
            ];

            $confirmed = $this->confirm("Everything's OK? Do you wish to continue?");

            try {
                if (! $confirmed) {
                    $this->error('Aborted');

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

    private function changeUserRoles()
    {
        $userActions = new UserActions();
        $roleActions = new RoleActions();

        $valid = false;

        $email = '';

        while (! $valid) {
            $email = $this->ask('Email:', $email);

            $usr = $userActions->readby('EMAIL', $email);

            if ($usr) {
                $this->info('User Name: '.$usr->name.'. Current Roles: '.$usr->roles()->pluck('display_name'));

                $mode = $this->choice('Do you want to attach or remove?', ['Attach', 'Remove']);

                $this->info('Available Roles: '.$roleActions->readAny()->pluck('display_name'));
                $roleDisplayName = $this->ask('Please Select From Available Roles: ');

                $role = $roleActions->readBy('DISPLAY_NAME', $roleDisplayName);

                if (! $role) {
                    $this->error('Invalid Role');

                    return false;
                }

                $confirmed = $this->confirm("Proceed to $mode Role $role->display_name to $usr->name?", true);

                if (! $confirmed) {
                    $this->error('Aborted');

                    return false;
                }

                if ($mode == 'Attach') {
                    $usr->attachRole($role);
                } elseif ($mode == 'Remove') {
                    $usr->detachRole($role);
                } else {
                }

                $this->info('Done');
                $this->info('User Name: '.$usr->name.'. Current Roles: '.$usr->roles()->pluck('display_name'));

                sleep(3);

                $confirmedExit = $this->confirm('Do you want to attach/remove another role?', false);

                if (! $confirmedExit) {
                    $this->error('Exiting');

                    return false;
                }
            }
        }
    }
}
