<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Database\Seeders\UserTableSeeder;
use Database\Seeders\RoleTableSeeder;
use Database\Seeders\CompanyTableSeeder;
use Database\Seeders\BranchTableSeeder;
use Illuminate\Console\ConfirmableTrait;

use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;
use function Laravel\Prompts\spin;

#[Signature('app:seed')]
#[Description('Interactive Data Seeding')]
class AppSeed extends Command
{
    use ConfirmableTrait;

    private array $availableSeeders = [
        'Default' => 'Default Data Seeder (Users, Roles, Companies, Branches)',
        'User'    => 'User Table Seeder',
        'Role'    => 'Role Table Seeder',
        'Company' => 'Company Table Seeder',
        'Branch'  => 'Branch Table Seeder',

        'Product' => 'Product Table Seeder',  

        'Cancel'  => 'Cancel Seeding Process',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return Command::FAILURE;
        }

        $selectedSeeders = multiselect(
            label: 'Select Seeders',
            options: $this->availableSeeders,
            default: ['Default'],
            scroll: 8,
            required: true,
        );

        if (in_array('Cancel', $selectedSeeders)) {
            $this->components->info('Seeding Process Cancelled.');
            return Command::SUCCESS;
        }

        if (in_array('Default', $selectedSeeders)) {
            $selectedSeeders = array_values(array_diff($selectedSeeders, ['User', 'Role', 'Company', 'Branch']));
        }

        foreach ($selectedSeeders as $seederKey) {
            $this->components->warn("--- {$this->availableSeeders[$seederKey]} ---");

            match ($seederKey) {
                'Default' => $this->runDefaultDataSeederNonInteractive(),

                'User'    => $this->runUserTableSeederInteractive(),
                'Role'    => $this->runRoleTableSeederInteractive(),
                'Company' => $this->runCompanyTableSeederInteractive(),
                'Branch'  => $this->runBranchTableSeederInteractive(),
            };
        }

        $this->output->newLine();
        $this->components->info('All Selected Seeding Completed!');
        return Command::SUCCESS;
    }

    private function runDefaultDataSeederNonInteractive(): void
    {
        spin(
            callback: fn() => (new UserTableSeeder())->callWith(UserTableSeeder::class, [true, 3]),
            message: 'Seeding 3 Users...'
        );
        $this->components->info('✓ UserTableSeeder Finished.');

        spin(
            callback: fn() => (new RoleTableSeeder())->callWith(RoleTableSeeder::class, [true, 2]),
            message: 'Seeding 2 Roles...'
        );
        $this->components->info('✓ RoleTableSeeder Finished.');

        spin(
            callback: fn() => (new CompanyTableSeeder())->callWith(CompanyTableSeeder::class, [3, 0]),
            message: 'Seeding 3 Companies per User...'
        );
        $this->components->info('✓ CompanyTableSeeder Finished.');

        spin(
            callback: fn() => (new BranchTableSeeder())->callWith(BranchTableSeeder::class, [3, 0]),
            message: 'Seeding 3 Branches per Company...'
        );
        $this->components->info('✓ BranchTableSeeder Finished.');
    }

    private function runUserTableSeederInteractive(): void
    {
        $truncate = confirm(label: 'Do you want to truncate the users table first?', default: false);
        $count = text(label: 'How many users to seed?', placeholder: '5', default: '5', required: true);

        spin(
            callback: fn() => (new UserTableSeeder())->callWith(UserTableSeeder::class, [(bool)$truncate, (int)$count]),
            message: 'Seeding  ' . $count . ' Users...'
        );
        $this->components->info('✓ UserTableSeeder Finished.');
    }

    private function runRoleTableSeederInteractive(): void
    {
        $count = text(label: 'How many roles to seed?', placeholder: '5', default: '5', required: true);

        spin(
            callback: fn() => (new RoleTableSeeder())->callWith(RoleTableSeeder::class, [true, (int)$count]),
            message: 'Seeding  ' . $count . ' Roles...'
        );
        $this->components->info('✓ RoleTableSeeder Finished.');
    }

    private function runCompanyTableSeederInteractive(): void
    {
        $companiesPerUsers = text(label: 'How many companies for each user?', placeholder: '3', default: '3', required: true);
        $userId = text(label: 'Only to this userId (0 for all):', placeholder: '0', default: '0', required: true);

        spin(
            callback: fn() => (new CompanyTableSeeder())->callWith(CompanyTableSeeder::class, [(int)$companiesPerUsers, (int)$userId]),
            message: 'Seeding  ' . $companiesPerUsers . ' Companies per User...'
        );
        $this->components->info('✓ CompanyTableSeeder Finished.');
    }

    private function runBranchTableSeederInteractive(): void
    {
        $branchPerCompanies = text(label: 'How many branches per company?', placeholder: '3', default: '3', required: true);
        $onlyThisCompanyId = text(label: 'Only for this companyId (0 for all):', placeholder: '0', default: '0', required: true);

        spin(
            callback: fn() => (new BranchTableSeeder())->callWith(BranchTableSeeder::class, [(int)$branchPerCompanies, (int)$onlyThisCompanyId]),
            message: 'Seeding  ' . $branchPerCompanies . ' Branches per Company...'
        );
        $this->components->info('✓ BranchTableSeeder Finished.');
    }
}
