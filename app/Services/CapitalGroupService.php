<?php

namespace App\Services;

interface CapitalGroupService
{
    public function create(
        $code,
        $name
    );

    public function read();

    public function update(
        $id,
        $code,
        $name
    );

    public function delete($id);

    public function checkDuplicatedCode($crud_status, $id, $code);
}
