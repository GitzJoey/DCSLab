<?php

namespace App\Services;

interface ProductBrandService
{
    public function create(
        $company_id,
        $code,
        $name
    );

    public function read($companyId, $search = '', $paginate = true, $perPage = 10);

    public function readBy($key, $value);

    public function update(
        $id,
        $company_id,
        $code,
        $name
    );

    public function delete($id);

    public function isUniqueCode($code, $userId, $exceptId);
}
