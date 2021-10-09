<?php

namespace App\Services;

interface InvestorService
{
    public function create(
        $code,
        $name,
        $term,
        $contact,
        $address,
        $city,
        $tax_number,
        $remarks,
        $status
    );

    public function read();

    public function update(
        $id,
        $code,
        $name,
        $term,
        $contact,
        $address,
        $city,
        $tax_number,
        $remarks,
        $status
    );

    public function delete($id);

    public function checkDuplicatedCode($crud_status, $id, $code);
}