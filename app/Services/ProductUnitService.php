<?php

namespace App\Services;

interface ProductUnitService
{
    public function create(
        $code,
        $name
    );

    public function read();

    public function getAllProductUnit();

    public function update(
        $id,
        $code,
        $name
    );

    public function delete($id);
}
