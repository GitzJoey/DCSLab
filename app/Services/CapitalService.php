<?php

namespace App\Services;

interface CapitalService
{
    public function create(
        $investor,
        $capital_group,
        $cash_id,
        $ref_number,
        $date,
        $amount,
        $remarks,
    );

    public function read();

    public function update(
        $id,
        $investor,
        $capital_group,
        $cash_id,
        $ref_number,
        $date,
        $amount,
        $remarks,
    );

    public function delete($id);

    public function checkDuplicatedCode($crud_status, $id, $code);
}