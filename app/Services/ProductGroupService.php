<?php

namespace App\Services;

interface ProductGroupService
{
    public function create(
        $code,
        $name
    );

    public function read();

    public function getAllProductGroup();

    public function update(
        $id,
        $code,
        $name
    );

    public function delete($id);
}
