<?php

namespace App\Services;

interface FinanceCashService
{
    public function create(
        $code,
        $name,
        $is_bank,
        $status
    );

    public function read();

    public function update(
        $id,
        $code,
        $name,
        $is_bank,
        $status
    );

    public function delete($id);

    public function checkDuplicatedCode($id, $code);
}