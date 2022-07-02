<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class DashboardPage extends Page
{
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/dashboard';
    }

    /**
     * Assert that the browser is on the page.
     *
     * @param  Browser  $browser
     * @return void
     */
    public function assert(Browser $browser)
    {
        $browser->assertPathIs($this->url());
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array
     */
    public function elements()
    {
        $navbar = $this->getNavBarElements();

        $elements = [

        ];

        return array_merge($elements, $navbar);
    }

    private function getNavBarElements()
    {
        return [
            '@side-menu-dashboard' => '[dusk="side-menu-dashboard"]',
            '@side-menu-dashboard-maindashboard' => '[dusk="side-menu-dashboard-maindashboard"]',
            '@side-menu-dashboard-demo' => '[dusk="side-menu-dashboard-demo"]',

            '@side-menu-company' => '[dusk="side-menu-company"]',
            '@side-menu-company-company' => '[dusk="side-menu-company-company"]',
            '@side-menu-company-branch' => '[dusk="side-menu-company-branch"]',
            '@side-menu-company-employee' => '[dusk="side-menu-company-employee"]',
            '@side-menu-company-warehouse' => '[dusk="side-menu-company-warehouse"]',

            '@side-menu-administrator' => '[dusk="side-menu-administrator"]',
            '@side-menu-administrator-user' => '[dusk="side-menu-administrator-user"]',

            '@side-menu-product' => '[dusk="side-menu-product"]',
            '@side-menu-product-product' => '[dusk="side-menu-product-product"]',
            '@side-menu-product-service' => '[dusk="side-menu-product-service"]',

            '@side-menu-supplier' => '[dusk="side-menu-supplier"]',
            '@side-menu-supplier-supplier' => '[dusk="side-menu-supplier-supplier"]',

            '@side-menu-purchase_order' => '[dusk="side-menu-purchase_order"]',
            '@side-menu-purchase_order-purchaseorder' => '[dusk="side-menu-purchase_order-purchaseorder"]',
        ];
    }
}
