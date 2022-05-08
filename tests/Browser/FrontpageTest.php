<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class FrontpageTest extends DuskTestCase
{
    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function test_frontpage_is_showing()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSee('DCSLab');
        });
    }

    public function test_registerpage_is_showing()
    {

    }

    public function test_loginpage_is_showing()
    {

    }

    public function test_resetpasswordpage_is_showing()
    {
        
    }
}
