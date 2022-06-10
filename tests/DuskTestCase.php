<?php

namespace Tests;

use App\Enums\UserRoles;
use App\Models\User;
use Database\Seeders\CompanyTableSeeder;
use Database\Seeders\UserTableSeeder;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Laravel\Dusk\TestCase as BaseTestCase;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        Parent::setUp();

        Artisan::call('db:wipe');

        if (!Schema::hasTable('users')) {
            Artisan::call('db:wipe');
            Artisan::call('migrate', ['--seed' => true]);
            
            $seed_user = new UserTableSeeder();
            $seed_user->callWith(UserTableSeeder::class, [false, 1]);
            $seed_user->callWith(UserTableSeeder::class, [false, 1, UserRoles::DEVELOPER]);

            $seed_company = new CompanyTableSeeder();
            $seed_company->callWith(CompanyTableSeeder::class, [2]);
        }

        $this->user = User::whereRelation('roles', 'name', '=', UserRoles::USER->value)->whereHas('companies')->inRandomOrder()->first();
        $this->developer = User::whereRelation('roles', 'name', '=', UserRoles::DEVELOPER->value)->first();
    }

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare()
    {
        if (! static::runningInSail()) {
            static::startChromeDriver();
        }
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        $options = (new ChromeOptions)->addArguments(collect([
            '--window-size=1920,1080',
        ])->unless($this->hasHeadlessDisabled(), function ($items) {
            return $items->merge([
                '--disable-gpu',
                '--headless',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }

    /**
     * Determine whether the Dusk command has disabled headless mode.
     *
     * @return bool
     */
    protected function hasHeadlessDisabled()
    {
        return isset($_SERVER['DUSK_HEADLESS_DISABLED']) ||
               isset($_ENV['DUSK_HEADLESS_DISABLED']);
    }
}
