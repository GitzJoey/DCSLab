<?php

namespace App\Services;

interface SystemService
{
    public function checkDBConnection();

    public function isExistTable($tableName);
}
