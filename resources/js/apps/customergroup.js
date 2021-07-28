import { createApp } from 'vue';
import { createI18n }from 'vue-i18n';
import CustomerGroup from './components/CustomerGroup';

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common["Accept"] = "application/json"
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.head.querySelector('meta[name="csrf-token"]').content;

let language = document.documentElement.lang;
window.axios.defaults.headers.common['X-localization'] = language;

const messages = {
    en: {
        table: {
            title: 'Customer Group Lists',
            cols: {
                code: 'Code',
                name: 'Name',
                is_member_card: 'Member Card',
                limit_outstanding_notes: 'Limit Outstanding Notes',
                limit_payable_nominal: 'Limit Payable Nominal',
                limit_due_date: 'Limit Due Date',
                term: 'Term',
                selling_point: 'Selling Point',
                selling_point_multiple: 'Selling Point Multiple',
                sell_at_capital_price: 'Sell At Capital Price',
                global_markup_percent: 'Global Markup Percent',
                global_markup_nominal: 'Global Markup Nominal',
                global_discount_percent: 'Global Discount Percent',
                global_discount_nominal: 'Global Discount Nominal',
                round_on: 'Round On',
                round_digit: 'Round Digit',
                remarks: 'Rermarks',
                finance_cash_id: ' Default Cash Payment',
            }
        },
        buttons: {
            submit: 'Submit',
            reset: 'Reset',
            back: 'Back',
            delete: 'Delete',
        },
        actions: {
            create: 'Create Customer Group',
            edit: 'Edit Customer Group',
            show: 'Show Customer Group',
            delete: 'Delete',
        },
        fields: {
            code: 'Code',
            name: 'Name',
            is_member_card: 'Member Card',
            use_limit_outstanding_notes: 'Use Limit Outstanding Notes',
            limit_outstanding_notes: 'Limit Outstanding Notes',
            use_limit_payable_nominal: 'Use Limit Payable Nominal',
            limit_payable_nominal: 'Limit Payable Nominal',
            use_limit_due_date: 'Use Due Date',
            limit_due_date: 'Limit Due Date',
            term: 'Term',
            selling_point: 'Selling Point',
            selling_point_multiple: 'Selling Point Multiple',
            sell_at_capital_price: 'Sell At Capital Price',
            global_markup_percent: 'Global Markup Percent',
            global_markup_nominal: 'Global Markup Nominal',
            global_discount_percent: 'Global Discount Percent',
            global_discount_nominal: 'Global Discount Nominal',
            is_rounding: 'Is Rounding',
            round_on: 'Round On',
            round_digit: 'Round Digit',
            remarks: 'Rermarks',
            finance_cash_id: ' Default Cash Payment',
            settings: {
                settings: 'Settings',
                theme: 'Themes',
                dateFormat: 'Date Format',
                timeFormat: 'Time Format'
            },
        },
        errors: {
            warning: 'Warning',
        },
        placeholder: {
            please_select: 'Please Select',
        },
        statusDDL: {
            active: 'Active',
            inactive: 'Inactive',
        }
    },
    id: {
        table: {
            title: 'Daftar Kelompok Pelanggan',
            cols: {
                code: 'Kode',
                name: 'Nama',
                is_member_card: 'Kartu Member',
                limit_outstanding_notes: 'Batas Nota',
                limit_payable_nominal: 'Batas Nominal',
                limit_due_date: 'Batas Hari',
                term: 'Tempo',
                selling_point: 'Poin Penjualan',
                selling_point_multiple: 'Kelipatan Poin Penjualan',
                sell_at_capital_price: 'Jual Dengan Harga Modal',
                global_markup_percent: 'Kenaikan Harga Global %',
                global_markup_nominal: 'Kenaikan Harga Global Rp',
                global_discount_percent: 'Diskon Global %',
                global_discount_nominal: 'Diskon Global Rp',
                is_rounding: 'Pembulatan',
                round_on: 'Pembulatan Ke',
                round_digit: 'Angka Pembulatan',
                remarks: 'Keterangan',
                finance_cash_id: 'Default Pembayaran',
            }
        },
        buttons: {
            submit: 'Simpan',
            reset: 'Reset',
            back: 'Kembali',
        },
        actions: {
            create: 'Tambah Kelompok Pelanggan',
            edit: 'Ubah Kelompok Pelanggan',
            show: 'Tampilkan Kelompok Pelanggan',
            delete: 'Hapus',
        },
        fields: {
            code: 'Kode',
            name: 'Nama',
            is_member_card: 'Kartu Member',
            use_limit_outstanding_notes: 'Menggunakan Batas Nota',
            limit_outstanding_notes: 'Batas Nota',
            use_limit_payable_nominal: 'Menggunakan Batas Nominal',
            limit_payable_nominal: 'Batas Nominal',
            use_limit_due_date: 'Menggunakan Batas Hari',
            limit_due_date: 'Batas Hari',
            term: 'Tempo',
            selling_point: 'Poin Penjualan',
            selling_point_multiple: 'Kelipatan Poin Penjualan',
            sell_at_capital_price: 'Jual Dengan Harga Modal',
            global_markup_percent: 'Kenaikan Harga Global %',
            global_markup_nominal: 'Kenaikan Harga Global Rp',
            global_discount_percent: 'Diskon Global %',
            global_discount_nominal: 'Diskon Global Rp',
            is_rounding: 'Pembulatan',
            round_on: 'Round On',
            round_digit: 'Round Digit',
            remarks: 'Keterangan',
            finance_cash_id: 'Default Pembayaran',
            settings: {
                settings: 'Pengaturan',
                theme: 'Tema',
                dateFormat: 'Format Tanggal',
                timeFormat: 'Format Waktu',
            },
        },
        errors: {
            warning: 'Peringatan',
        },
        placeholder: {
            please_select: 'Silahkan Pilih',
        },
        statusDDL: {
            active: 'Aktif',
            inactive: 'Tidak Aktif',
        }
    }
};

const i18n = createI18n({
    locale: language,
    fallbackLocale: 'en',
    messages,
});

createApp(CustomerGroup)
    .use(i18n)
    .mount('#customergroupVue')
