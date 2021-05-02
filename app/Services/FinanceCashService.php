<?php

namespace App\Services;

interface FinanceCashService
{
    public function create(
        $code,
        $name,
        $is_bank,
        $is_active
    );

    public function read();

    public function update(
        $code,
        $name,
        $is_bank,
        $is_active
    );

    public function delete($id);
}