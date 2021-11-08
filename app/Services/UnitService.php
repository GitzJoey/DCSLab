<?php

namespace App\Services;

interface UnitService
{
    public function create(
        // $company_id,
        $code,
        $name,
        $category
    );

    public function read();

    public function getAllUnit();
    
    public function getAllProduct();

    public function getAllService();

    public function GetAllProductandService();

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
