<?php

namespace App\Actions\Dashboard;

use App\Enums\UserRole;
use App\Traits\CacheHelper;
use Illuminate\Support\Facades\Auth;

class DashboardActions
{
    use CacheHelper;

    public function __construct() {}

    public function createUserMenu(bool $useCache = true): array
    {
        $cacheKey = '';
        if ($useCache) {
            $cacheKey = 'menu_'.Auth::id();
            $cacheResult = $this->readFromCache($cacheKey);

            if (! is_null($cacheResult)) {
                return $cacheResult;
            }
        }

        $menu = [];

        $usr = Auth::user();

        $usrRoles = $usr->roles;

        $hasUserRole = $usrRoles->where('name', UserRole::USER->value)->isNotEmpty() ? true : false;
        $hasOnlyUserRole = $usrRoles->where('name', UserRole::USER->value)->isNotEmpty() && $usrRoles->count() == 1 ? true : false;

        $hasAdminRole = $usrRoles->where('name', UserRole::ADMINISTRATOR->value)->isNotEmpty() ? true : false;
        $hasOnlyAdminRole = $usrRoles->where('name', UserRole::ADMINISTRATOR->value)->isNotEmpty() && $usrRoles->count() == 1 ? true : false;

        $hasDevRole = $usrRoles->where('name', UserRole::DEVELOPER->value)->isNotEmpty() ? true : false;

        $hasCompany = $usr->companies->count() != 0 ? true : false;

        $showDemoMenu = false;

        $menu = $this->createMenu_Dashboard($menu, $showDemoMenu);
        $menu = $this->createMenu_Company($menu, $hasOnlyUserRole, $hasOnlyAdminRole, $hasCompany, $hasDevRole);
        $menu = $this->createMenu_Administrator($menu, $hasAdminRole, $hasDevRole);
        $menu = $this->createMenu_DevTool($menu, $hasDevRole);

        $this->saveToCache($cacheKey, $menu);

        return $menu;
    }

    private function createMenu_Dashboard(array $menu, bool $showDemo): array
    {
        $maindashboard = [
            'icon' => 'ChevronRight',
            'route_name' => 'dashboard-maindashboard',
            'title' => 'components.menu.main-dashboard',
        ];

        $demo = [
            'icon' => 'ChevronRight',
            'route_name' => 'dashboard-demo',
            'title' => 'components.menu.demo',
        ];

        $root_array = [
            'icon' => 'Home',
            'route_name' => 'dashboard-dashboard',
            'title' => 'components.menu.dashboard',
            'sub_menu' => [],
        ];

        if ($showDemo) {
            array_push($root_array['sub_menu'], $maindashboard, $demo);
        } else {
            array_push($root_array['sub_menu'], $maindashboard);
        }

        array_push($menu, $root_array);

        return $menu;
    }

    private function createMenu_Company(array $menu, bool $hasOnlyUserRole, bool $hasOnlyAdminRole, bool $hasCompany, bool $hasDevRole): array
    {
        if ($hasOnlyUserRole || $hasOnlyAdminRole) {
            return $menu;
        }

        $company = [
            'icon' => 'ChevronRight',
            'route_name' => 'dashboard-company-company',
            'title' => 'components.menu.company-company',
        ];

        $branches = [
            'icon' => 'ChevronRight',
            'route_name' => 'company-branch',
            'title' => 'components.menu.company-branch',
        ];

        $root_array = [
            'icon' => 'Umbrella',
            'route_name' => 'company',
            'title' => 'components.menu.company',
            'sub_menu' => [],
        ];

        if ($hasCompany || $hasDevRole) {
            array_push($root_array['sub_menu'], $company, $branches);
        } else {
            array_push($root_array['sub_menu'], $company);
        }

        array_push($menu, $root_array);

        return $menu;
    }

    private function createMenu_Administrator(array $menu, bool $hasAdminRole, bool $hasDevRole): array
    {
        $user = [
            'icon' => 'ChevronRight',
            'route_name' => 'administrator-user',
            'title' => 'components.menu.administrator-user',
        ];

        $root_array = [
            'icon' => 'Cpu',
            'route_name' => 'administrator',
            'title' => 'components.menu.administrator',
            'sub_menu' => [],
        ];

        array_push($root_array['sub_menu'], $user);

        if ($hasAdminRole || $hasDevRole) {
            array_push($menu, $root_array);
        }

        return $menu;
    }

    private function createMenu_DevTool(array $menu, bool $hasDevRole): array
    {
        $devtool = [
            'icon' => 'ChevronRight',
            'route_name' => 'devtool-devtool',
            'title' => 'components.menu.devtool-devtool',
        ];

        $playground = [
            'icon' => 'ChevronRight',
            'route_name' => 'devtool-playground',
            'title' => 'components.menu.devtool-playground',
            'sub_menu' => [],
        ];

        $playground_ex1 = [
            'icon' => 'ChevronsRight',
            'route_name' => 'devtool-playground-p1',
            'title' => 'components.menu.devtool-playground-p1',
        ];

        $playground_ex2 = [
            'icon' => 'ChevronsRight',
            'route_name' => 'devtool-playground-p2',
            'title' => 'components.menu.devtool-playground-p2',
        ];

        array_push($playground['sub_menu'], $playground_ex1);
        array_push($playground['sub_menu'], $playground_ex2);

        $root_array = [
            'icon' => 'Github',
            'route_name' => 'devtool',
            'title' => 'components.menu.devtool',
            'sub_menu' => [],
        ];

        array_push($root_array['sub_menu'], $devtool);
        array_push($root_array['sub_menu'], $playground);

        if ($hasDevRole) {
            array_push($menu, $root_array);
        }

        return $menu;
    }
}
