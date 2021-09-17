<?php

namespace App\Console\Commands;

use App\Services\RoleService;
use App\Services\UserService;
use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class AppHelper extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:helper';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Helper for this applications';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!File::exists('.env')) {
            $this->error('File Not Found: .env');
            $this->error('Aborted');
            return false;
        }

        $loop = true;

        while($loop)
        {
            $this->info('Available Helper:');
            $this->info('[1] Update Composer And NPM');
            $this->info('[2] Clear All Cache');
            $this->info('[3] Change User Roles');
            $this->info('[X] Exit');

            $choose = $this->ask('Choose Helper','X');

            switch (strtoupper($choose)) {
                case 1:
                    $this->updateComposerAndNPM();
                    $this->info('Done!');
                    break;
                case 2:
                    $this->clearCache();
                    $this->info('Done!');
                    break;
                case 3:
                    $this->changeUserRoles();
                    $this->info('Done!');
                    break;
                case 'X':
                default:
                    $loop = false;
                    break;
            }
            sleep(3);
        }
        $this->info('Bye!');
    }

    private function changeUserRoles()
    {
        $container = Container::getInstance();
        $userService = $container->make(UserService::class);
        $roleService = $container->make(RoleService::class);

        $valid = false;

        $email = '';

        while (!$valid) {
            $email = $this->ask('Email:', $email);

            $usr = $userService->getUserByEmail($email);

            if ($usr) {
                $this->info('User Name: '.$usr->name.'. Current Roles: '.$usr->roles()->pluck('display_name'));

                $mode = $this->choice('Do you want to attach or remove?', ['Attach', 'Remove']);

                $this->info('Available Roles: '.$roleService->read()->pluck('display_name'));
                $roleDisplayName = $this->ask('Please Select From Available Roles: ');

                $role = $roleService->getRoleByDisplayName($roleDisplayName, false);

                if (!$role) {
                    $this->error('Invalid Role');
                    return false;
                }

                $confirmed = $this->confirm("Proceed to $mode Role $role->display_name to $usr->name?", true);

                if (!$confirmed) {
                    $this->error('Aborted');
                    return false;
                }

                if ($mode == 'Attach') {
                    $usr->attachRole($role);
                } else if ($mode == 'Remove') {
                    $usr->detachRole($role);
                } else {

                }

                $this->info('Done');
                $this->info('User Name: '.$usr->name.'. Current Roles: '.$usr->roles()->pluck('display_name'));

                sleep(3);

                $confirmedExit = $this->confirm("Do you want to attach/remove another role?", false);

                if (!$confirmedExit) {
                    $this->error('Exiting');
                    return false;
                }
            }
        }
    }

    private function clearCache()
    {
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        Artisan::call('debugbar:clear');
        Artisan::call('cache:clear');
        Artisan::call('clear-compiled');
    }

    private function updateComposerAndNPM()
    {
        $this->info('Starting Composer Update');
        exec('composer update');

        $this->info('Starting NPM Update');
        exec('npm update');

        $this->info('Starting Mix');
        if (App::environment('prod', 'production')) {
            $this->info('Executing for production enviroment');
            exec('npm run prod');
        } else {
            exec('npm run dev');
        }
    }
}
