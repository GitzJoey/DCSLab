<?php

namespace App\Services\Impls;

use App\Services\SystemService;
use Exception;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemServiceImpl implements SystemService
{
    public function __construct()
    {
    }

    public function checkDBConnection(): bool
    {
        try {
            $this->getConnection()->getPdo();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getDBConnectionError(): string
    {
        try {
            $this->getConnection()->getPdo();

            return 'success';
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    private function getConnection(): Connection
    {
        return DB::connection();
    }

    public function isExistTable(string $tableName = null): bool
    {
        if ($tableName == null) {
            return Schema::hasTable('users');
        } else {
            return Schema::hasTable($tableName);
        }
    }
}
