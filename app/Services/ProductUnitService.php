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
        $is_primary_unit,
        $remarks
    );

    public function read($userId);

    public function getAllProductUnit();

    public function update(
        $id,
        $code,
        $company_id,
        $product_id,
        $unit_id,
        $is_base,
        $conversion_value,
        $is_primary_unit,
        $remarks
    );

    public function delete($id);

    public function checkDuplicatedCode($crud_status, $id, $code);
}
