<?php

namespace App\Services;

interface ProductGroupService
{
    public function create(
        $company_id,
        $code,
        $name,
        $category,
    );

    public function read();

    public function getAllProductGroup_Product();

    public function getAllProductGroup_Service();

    public function update(
        $id,
        $company_id,
        $code,
        $name,
        $category
    );

    public function delete($id);

    public function checkDuplicatedCode($crud_status, $id, $code);
}
