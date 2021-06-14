import { createApp } from 'vue';
import { createI18n }from 'vue-i18n';
import Product from './components/Product';

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common["Accept"] = "application/json"
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.head.querySelector('meta[name="csrf-token"]').content;

let language = document.documentElement.lang;
window.axios.defaults.headers.common['X-localization'] = language;

const messages = {
    en: {
        table: {
            title: 'Product Lists',
            cols: {
                code: 'Code',
                group_name: 'Group Name',
                brand_name: 'Brand Name',
                name: 'Name',
                unit_name: 'Unit Name',
                price: 'Price',
                tax: 'Tax',
                information: 'Information',
                estimated_capital_price: 'Estimated Capital Price',
                point: 'Point',
                is_use_serial: 'Is Use Serial',
                is_buy: 'Is Buy',
                is_production_material: 'Is Production Material',
                is_production_result: 'Is Production Result',
                is_sell: 'Is Sell',
                status: 'Status',
                
            }
        },
        buttons: {
            submit: 'Submit',
            reset: 'Reset',
            back: 'Back',
        },
        actions: {
            create: 'Create Product',
            edit: 'Edit Product',
            show: 'Show Product',
        },
        fields: {
            code: 'Code',
            group_name: 'Group Name',
            brand_name: 'Brand Name',
            name: 'Name',
            unit_name: 'Unit Name',
            price: 'Price',
            tax: 'Tax',
            information: 'Information',
            estimated_capital_price: 'Estimated Capital Price',
            point: 'Point',
            is_use_serial: 'Is Use Serial',
            is_buy: 'Is Buy',
            is_production_material: 'Is Production Material',
            is_production_result: 'Is Production Result',
            is_sell: 'Is Sell',
            status: 'Status',
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
        },
        bankDDL: {
            active: 'Active',
            inactive: '-',
        },
    },
    id: {
        table: {
            title: 'Daftar Product',
            cols: {
                code: 'Kode',
                group_name: 'Nama Group',
                brand_name: 'Nama Merk',
                name: 'Nama',
                unit_name: 'Nama Satuan',
                price: 'Harga',
                tax: 'Ppn',
                information: 'Informasi',
                estimated_capital_price: 'Perkiraan Harga Modal',
                point: 'Poin',
                is_use_serial: 'Memakai Nomor Serial',
                is_buy: 'Beli',
                is_production_material: 'Bahan Produksi',
                is_production_result: 'Hasil Produksi',
                is_sell: 'Jual',
                status: 'Status',
            }
        },
        buttons: {
            submit: 'Simpan',
            reset: 'Reset',
            back: 'Kembali',
        },
        actions: {
            create: 'Tambah Product',
            edit: 'Ubah Product',
            show: 'Tampilkan Product',
            reset_password: 'Reset Password',
        },
        fields: {
            code: 'Kode',
            group_name: 'Nama Group',
            brand_name: 'Nama Merk',
            name: 'Nama',
            unit_name: 'Nama Satuan',
            price: 'Harga',
            tax: 'Ppn',
            information: 'Informasi',
            estimated_capital_price: 'Perkiraan Harga Modal',
            point: 'Poin',
            is_use_serial: 'Memakai Nomor Serial',
            is_buy: 'Beli',
            is_production_material: 'Bahan Produksi',
            is_production_result: 'Hasil Produksi',
            is_sell: 'Jual',
            status: 'Status',
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
        },
    }
};

const i18n = createI18n({
    locale: language,
    fallbackLocale: 'en',
    messages,
});

createApp(Product)
    .use(i18n)
    .mount('#productVue')
