<?php

namespace App\Services;

interface ActivityLogService
{
    public function ViewActivity(
        $viewName
    );

    public function ActionActivity(
        $type,
        $controller,
        $method
    );
}
