<?php

return [
    'unique_code' => 'Code sudah pernah dipakai',
    'unique_name' => 'Nama sudah pernah dipakai',
    'unique_address' => 'Alamat sudah pernah dipakai',
    'unique_slug' => 'Slug sudah pernah dipakai',
    'valid_dropdown' => 'Nilai tidak valid',
    'valid_company' => 'Perusahaan tidak valid',
    'valid_branch' => 'Cabang tidak valid',
    'valid_warehouse' => 'Gudang tidak valid',
    'valid_customer_group' => 'Kelompok pelanggan tidak valid',
    'valid_customer' => 'Pelanggan tidak valid',
    'too_many_tokens' => 'Terlalu banyak permintaan token',
    'must_reset_password' => 'Harap lakukan reset password',
    'inactive_user' => 'Profil anda tidak aktif',
    'company' => [
        'deactivate_default_company' => 'Perusahaan utama tidak boleh dinonaktifkan',
        'delete_default_company' => 'Perusahaan utama tidak boleh dihapus',
        'set_company_to_non_default' => 'Tidak di ijinkan merubah default company',
    ],
    'branch' => [
        'delete_main_branch' => 'Cabang utama tidak boleh dihapus',
        'set_branch_to_non_main' => 'Tidak di ijinkan merubah cabang utama',
    ],
    'product' => [
        'unit' => [
            'duplicate_conversion' => 'Dalam satu produk, conversion value tidak boleh duplikat.',
            'duplicate_unit' => 'Dalam satu produk, unit tidak boleh duplikat.',
            'single_base' => 'Dalam satu produk harus ada tepat satu base unit.',
            'single_primary' => 'Dalam satu produk harus ada tepat satu primary unit.',
            'duplicate_code' => 'Dalam satu produk, kode unit (SKU) tidak boleh duplikat.',
            'base_conversion_must_be_one' => 'Conversion value untuk base unit harus bernilai 1.',
            'non_base_conversion_must_gt_one' => 'Conversion value untuk unit non-base harus lebih dari 1.',
            'base_price_inconsistent' => 'Harga per satuan dasar tidak konsisten antar unit.',
            'cannot_delete_base_unit' => 'Base unit tidak boleh dihapus.',
        ],
        'vat' => [
            'must_be_zero_if_not_taxable' => 'VAT rate harus 0 jika produk tidak dikenai pajak.',
            'out_of_range' => 'VAT rate harus di antara 0 hingga 100.',
        ],
    ],
];
