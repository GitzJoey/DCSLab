<?php

namespace App\Services;

interface SupplierService
{
    public function create(
        $company_id,
        $code,
        $name,
        $term,
        $contact,
        $address,
        $city,
        $is_tax,
        $tax_number,
        $remarks,
        $status
    );

    public function read($companyId, $search = '', $paginate = true, $perPage = 10);

    public function update(
        $id,
        $code,
        $name,
        $term,
        $contact,
        $address,
        $city,
        $is_tax,
        $tax_number,
        $remarks,
        $status
    );

    public function delete($id);

    public function isUniqueCode($code, $userId, $exceptId);
}
