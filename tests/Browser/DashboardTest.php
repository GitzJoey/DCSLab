<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\DashboardPage;
use Tests\DuskTestCase;

class DashboardTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function test_dashboard_is_showing()
    {
        $loggedInUser = $this->developer;

        $this->browse(function (Browser $browser) use ($loggedInUser) {
            $browser->loginAs($loggedInUser)
                    ->visit(new DashboardPage)
                    ->click('@side-menu-company')
                    ->waitFor('@side-menu-company-company')
                    ->click('@side-menu-company-company')
                    ->assertSee('Company Lists');
        });
    }
}
