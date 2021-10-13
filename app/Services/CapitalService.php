<?php

namespace App\Services;

interface CapitalService
{
    public function create(
        $ref_number,
        $investor_id,
        $group_id,
        $cash_id,
        $date,
        $capital_status,
        $amount,
        $remarks,
    );

    public function read();

    public function update(
        $id,
        $ref_number,
        $investor_id,
        $group_id,
        $cash_id,    
        $date,
        $capital_status,
        $amount,
        $remarks,
    );

    public function delete($id);

    public function checkDuplicatedCode($crud_status, $id, $code);
}