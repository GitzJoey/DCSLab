<?php

namespace App\Services;

interface CashService
{
    public function create(
        $code,
        $name,
        $is_bank,
        $status
    );

    public function read($userId);

    public function getAllActiveCash();

    public function update(
        $id,
        $code,
        $name,
        $is_bank,
        $status
    );

    public function delete($id);

    public function checkDuplicatedCode($crud_status, $id, $code);
}