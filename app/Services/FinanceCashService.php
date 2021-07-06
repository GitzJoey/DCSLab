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
        $code,
        $name,
        $is_bank,
        $status
    );

    public function delete($id);
}