<?php

namespace App\Services;

interface SystemService
{
    public function checkDBConnection(): bool;

    public function isExistTable(string $tableName): bool;
}
