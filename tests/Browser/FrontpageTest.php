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
}
