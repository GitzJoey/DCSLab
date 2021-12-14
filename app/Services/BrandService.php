<?php

namespace App\Services;

interface BrandService
{
    public function create(
        $company_id,
        $code,
        $name
    );

    public function read();

    public function getAllBrand();

    public function update(
        $id,
        $company_id,
        $code,
        $name
    );

    public function delete($id);

    public function checkDuplicatedCode($crud_status, $id, $code);
}