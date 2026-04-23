<?php

return [
    'unique_code' => 'Code sudah pernah dipakai',
    'unique_name' => 'Nama sudah pernah dipakai',
    'unique_address' => 'Alamat sudah pernah dipakai',
    'valid_dropdown' => 'Nilai tidak valid',
    'valid_company' => 'Perusahaan tidak valid',
    'valid_branch' => 'Cabang tidak valid',
    'valid_warehouse' => 'Gudang tidak valid',
    'valid_stock_adjustment_in_item' => 'Penyesuaian stok masuk per item tidak valid',
    'valid_stock_adjustment_out_item' => 'Penyesuaian stok keluar per item tidak valid',
    'valid_customer_group' => 'Kelompok pelanggan tidak valid',
    'valid_customer' => 'Pelanggan tidak valid',
    'valid_supplier' => 'Pemasok tidak valid',
    'valid_cash_account' => 'Akun kas tidak valid',
    'too_many_tokens' => 'Terlalu banyak permintaan token',
    'must_reset_password' => 'Harap lakukan reset password',
    'inactive_user' => 'Profil anda tidak aktif',
    'purchase_order' => [
        'exceed_available_down_payment' => 'Total refund down payment melebihi down payment yang tersedia',
    ],
    'company' => [
        'deactivate_default_company' => 'Perusahaan utama tidak boleh dinonaktifkan',
        'delete_default_company' => 'Perusahaan utama tidak boleh dihapus',
        'set_company_to_non_default' => 'Tidak di ijinkan merubah default company',
    ],
    'branch' => [
        'delete_main_branch' => 'Cabang utama tidak boleh dihapus',
        'set_branch_to_non_main' => 'Tidak di ijinkan merubah cabang utama',
    ],
    'expense_category' => [
        'parent_must_be_active' => 'Parent expense category harus aktif.',
        'cannot_delete_with_children' => 'Expense category tidak boleh dihapus karena masih memiliki child category.',
        'must_not_have_children' => 'Expense category harus memilih category yang tidak memiliki child lagi.',
    ],
    'income_category' => [
        'parent_must_be_active' => 'Parent income category harus aktif.',
        'cannot_delete_with_children' => 'Income category tidak boleh dihapus karena masih memiliki child category.',
        'must_not_have_children' => 'Income category harus memilih category yang tidak memiliki child lagi.',
    ],
    'expense' => [
        'amount_total_must_be_positive' => 'Jumlah bayar langsung atau jumlah terhutang harus lebih dari nol.',
        'payments_exceed_amount_payable' => 'Total pembayaran hutang tidak boleh melebihi jumlah terhutang.',
        'invalid_payment_reference' => 'Data pembayaran expense tidak valid untuk expense ini.',
    ],
    'income' => [
        'amount_total_must_be_positive' => 'Jumlah diterima langsung atau jumlah piutang harus lebih dari nol.',
        'payments_exceed_amount_receivable' => 'Total pembayaran piutang tidak boleh melebihi jumlah piutang.',
        'invalid_payment_reference' => 'Data pembayaran pendapatan tidak valid untuk pendapatan ini.',
    ],
    'prepaid_expense' => [
        'amount_total_must_be_positive' => 'Jumlah bayar langsung atau jumlah terhutang harus lebih dari nol.',
        'payments_exceed_amount_payable' => 'Total pembayaran hutang tidak boleh melebihi jumlah terhutang.',
        'invalid_payment_reference' => 'Data pembayaran biaya dibayar dimuka tidak valid untuk transaksi ini.',
    ],
    'debt' => [
        'amount_total_must_be_positive' => 'Jumlah pencairan atau jumlah hutang bawaan harus lebih dari nol.',
        'payments_exceed_total_debt' => 'Total pembayaran hutang tidak boleh melebihi total hutang.',
        'invalid_payment_reference' => 'Data pembayaran hutang tidak valid untuk transaksi ini.',
        'party_must_be_single' => 'Pilih salah satu pihak saja, kreditur atau pemasok.',
        'party_is_required' => 'Kreditur atau pemasok wajib dipilih.',
        'cash_account_is_required_for_direct_amount_received' => 'Akun kas wajib dipilih jika ada nominal diterima langsung.',
        'direct_amount_received_is_required_for_cash_account' => 'Nominal diterima langsung wajib lebih dari nol jika akun kas diisi.',
    ],
    'debt_payment' => [
        'invalid_debt_reference' => 'Data hutang tidak valid untuk cabang yang dipilih.',
    ],
    'prepaid_income' => [
        'amount_total_must_be_positive' => 'Jumlah diterima langsung atau jumlah piutang harus lebih dari nol.',
        'payments_exceed_amount_receivable' => 'Total pembayaran piutang tidak boleh melebihi jumlah piutang.',
        'invalid_payment_reference' => 'Data pembayaran pendapatan diterima dimuka tidak valid untuk transaksi ini.',
    ],
    'stock_adjustment' => [
        'invalid_in_item_reference' => 'Data item barang masuk tidak valid untuk stock adjustment ini.',
        'invalid_out_item_reference' => 'Data item barang keluar tidak valid untuk stock adjustment ini.',
        'invalid_in_item_serial_reference' => 'Data serial barang masuk tidak valid untuk stock adjustment ini.',
        'invalid_out_item_serial_reference' => 'Data serial barang keluar tidak valid untuk stock adjustment ini.',
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
