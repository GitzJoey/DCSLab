<?php

namespace App\Services;

interface HouseService
{
    public function create(
        $code,
        $name,
        $status
    );

    public function read();

    public function update(
        $id,
        $code,
        $name,
        $status
    );

    public function delete($id);
}