<?php

namespace App\Services;

interface ProductUnitService
{
    public function create(
        $code,
        $company_id,
        $product_id,
        $unit_id,
        $is_base,
        $conversion_value,
        $remarks
    );

    public function read();

    public function getAllActiveProductUnit();

    public function update(
        $id,
        $code,
        $company_id,
        $product_id,
        $unit_id,
        $is_base,
        $conversion_value,
        $remarks
    );

    public function delete($id);

    public function checkDuplicatedCode($crud_status, $id, $code);
}
