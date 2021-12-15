<?php

namespace App\Services;

interface UnitService
{
    public function create(
        $company_id,
        $code,
        $name,
        $category
    );

    public function read();
    
    public function getAllUnit_Product();

    public function getAllUnit_Service();

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