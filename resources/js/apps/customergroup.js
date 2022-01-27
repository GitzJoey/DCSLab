import { createApp } from 'vue';
import { ZiggyVue } from 'ziggy';
import { Ziggy } from '../ziggy';
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
                max_open_invoice: 'Max Open Invoice',
                max_outstanding_invoice: 'Max Outstanding Invoice',
                max_invoice_age: 'Max Invoice Age',
                payment_term: 'Payment Term',
                selling_point: 'Selling Point',
                selling_point_multiple: 'Selling Point Multiple',
                sell_at_cost: 'Sell At Cost',
                price_markup_percent: 'Price Markup Percent',
                price_markup_nominal: 'Price Markup Nominal',
                price_markdown_percent: 'Price Markdown Percent',
                price_markdown_nominal: 'Price Markdown Nominal',
                round_on: 'Round On',
                round_digit: 'Round Digit',
                remarks: 'Remarks',
                cash_id: ' Default Cash Payment',
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
            max_open_invoice: 'Max Open Invoice',
            max_outstanding_invoice: 'Max Outstanding Invoice',
            max_invoice_age: 'Max Invoice Age',
            payment_term: 'Payment Term',
            selling_point: 'Selling Point',
            selling_point_multiple: 'Selling Point Multiple',
            sell_at_cost: 'Sell At Cost',
            price_markup_percent: 'Price Markup Percent',
            price_markup_nominal: 'Price Markup Nominal',
            price_markdown_percent: 'Price Markdown Percent',
            price_markdown_nominal: 'Price Markdown Nominal',
            round_on: 'Round On',
            round_digit: 'Round Digit',
            remarks: 'Remarks',
            cash_id: ' Default Cash Payment',
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
            data_not_found: 'Data Not Found',
            please_select: 'Please Select',
        },
        tax_statusDLL: {
            excudetax: 'Exclude Tax',
            includetax: 'Include Tax'
        },
        round_onDLL: {
            none: 'None',
            high: 'High',
            low: 'Low',
        },
        sell_at_cost: {
            active: 'Yes',
            inactive: 'No',
        },
        statusDDL: {
            active: 'Active',
            inactive: 'Inactive',
        },
    },
    id: {
        table: {
            title: 'Daftar Kelompok Pelanggan',
            cols: {
                code: 'Kode',
                name: 'Nama',
                max_open_invoice: 'Batas Nota',
                max_outstanding_invoice: 'Batas Nominal',
                max_invoice_age: 'Batas Hari',
                payment_term: 'Tempo',
                selling_point: 'Poin Penjualan',
                selling_point_multiple: 'Kelipatan Poin Penjualan',
                sell_at_cost: 'Jual Dengan Harga Modal',
                price_markup_percent: 'Kenaikan Harga Global %',
                price_markup_nominal: 'Kenaikan Harga Global Rp',
                price_markdown_percent: 'Diskon Global %',
                price_markdown_nominal: 'Diskon Global Rp',
                round_on: 'Pembulatan Ke',
                round_digit: 'Angka Pembulatan',
                remarks: 'Keterangan',
                cash_id: 'Default Pembayaran',
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
            max_open_invoice: 'Batas Nota',
            max_outstanding_invoice: 'Batas Nominal',
            max_invoice_age: 'Batas Hari',
            payment_term: 'Tempo',
            selling_point: 'Poin Penjualan',
            selling_point_multiple: 'Kelipatan Poin Penjualan',
            sell_at_cost: 'Jual Dengan Harga Modal',
            price_markup_percent: 'Kenaikan Harga Global %',
            price_markup_nominal: 'Kenaikan Harga Global Rp',
            price_markdown_percent: 'Diskon Global %',
            price_markdown_nominal: 'Diskon Global Rp',
            round_on: 'Round On',
            round_digit: 'Round Digit',
            remarks: 'Keterangan',
            cash_id: 'Default Pembayaran',
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
            data_not_found: 'Data Tidak Ditemukan',
            please_select: 'Silahkan Pilih',
        },
        round_onDLL: {
            none: 'None',
            high: 'Atas',
            low: 'Bawah',
        },
        sell_at_cost: {
            active: 'Ya',
            inactive: 'Tidak',
        },
        statusDDL: {
            active: 'Aktif',
            inactive: 'Tidak Aktif',
        },
    }
};

const i18n = createI18n({
    locale: language,
    fallbackLocale: 'en',
    messages,
});

createApp(CustomerGroup)
    .use(ZiggyVue, Ziggy)
    .use(i18n)
    .mount('#customergroupVue')
