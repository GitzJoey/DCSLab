<?php

namespace App\Services;

interface ProductBrandService
{
    public function create(
        $code,
        $name
    );

    public function read();

    public function update(
        $code,
        $name
    );

    public function delete($id);
}
