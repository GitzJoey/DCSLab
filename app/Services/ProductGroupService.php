<?php

namespace App\Services;

interface ProductGroupService
{
    public function create(
        $code,
        $name
    );

    public function read($userId);

    public function getAllActiveProductGroup();

    public function update(
        $id,
        $code,
        $name
    );

    public function delete($id);

    public function checkDuplicatedCode($crud_status, $id, $code);
}
