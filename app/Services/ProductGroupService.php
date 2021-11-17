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

    public function read($userId);

    public function getAllProductGroup();

    public function getAllProduct();

    public function getAllService();

    public function GetAllProductandService();

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
