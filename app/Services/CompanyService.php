<?php

namespace App\Services;

interface CompanyService
{
    public function create(
        $code,
        $name,
        $is_active
    );

    public function read();

    public function update(
        $code,
        $name,
        $is_active
    );

    public function delete($id);
}