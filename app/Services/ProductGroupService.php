<?php

namespace App\Services;

interface ProductGroupService
{
    public function create(
        // $company_id,
        $code,
        $name,
        $category
    );

    public function read();

    public function getAllActiveProductGroup();

    public function getAllProductGroup();

    public function getAllServiceGroup();

    public function GetAllProductandServiceGroup();

    public function update(
        $id,
        // $company_id,
        $code,
        $name,
        $category
    );

    public function delete($id);

    public function checkDuplicatedCode($crud_status, $id, $code);
}
