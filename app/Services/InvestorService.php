<?php

namespace App\Services;

interface InvestorService
{
    public function create(
        $code,
        $name,
        $contact,
        $address,
        $city,
        $tax_number,
        $remarks,
        $status
    );

    public function read();

    public function getAllActiveInvestor();

    public function update(
        $id,
        $code,
        $name,
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