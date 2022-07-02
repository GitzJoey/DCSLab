<?php

namespace App\Services;

interface DashboardService
{
    public function createMenu(bool $useCache = true): array;

    public function clearUserCache($userId): bool;
}
