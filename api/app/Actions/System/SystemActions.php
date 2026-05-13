<?php

namespace App\Actions\System;

use Exception;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;

class SystemActions
{
    public function checkDBConnection(): bool
    {
        try {
            return (bool) $this->getConnection()->getPdo();
        } catch (Exception) {
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

    public function isExistTable(?string $tableName = null): bool
    {
        return Schema::hasTable($tableName ?? 'users');
    }

    public function checkRedisConnection(): bool
    {
        $redis = Redis::connection();
        try {
            $redis->ping();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function getConnection(): Connection
    {
        return DB::connection();
    }
}
