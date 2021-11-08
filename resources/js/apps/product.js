import { createApp } from 'vue';
import { ZiggyVue } from 'ziggy';
import { Ziggy } from '../ziggy';
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
                group_id: 'Group Name',
                brand_id: 'Brand Name',
                name: 'Name',
                supplier_id: 'Supplier',
                tax_status: 'Tax',
                remarks: 'Remarks',
                estimated_capital_price: 'Estimated Capital Price',
                point: 'Point',
                is_use_serial: 'Is Use Serial',
                product_type: 'Product Type',
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
            delete: 'Delete',
        },
        fields: {
            code: 'Code',
            group_id: 'Group Name',
            brand_id: 'Brand Name',
            name: 'Name',
            supplier_id: 'Supplier',
            tax_status: 'Tax',
            remarks: 'Remarks',
            estimated_capital_price: 'Estimated Capital Price',
            point: 'Point',
            is_use_serial: 'Is Use Serial',
            product_type: 'Product Type',
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
        tax_statusDDL: {
            notax: 'No Tax',
            excudetax: 'Exclude Tax',
            includetax: 'Include Tax'
        },
        product_typeDDL: {
            rawmaterial: 'RAW Material',
            wip: 'WIP',
            finishedgoods: 'Finished Goods',
            service: 'Service',
        },
        is_use_serial: {
            active: 'Yes',
            inactive: 'No',
        },
        statusDDL: {
            active: 'Active',
            inactive: 'Inactive'
        }
    },
    id: {
        table: {
            title: 'Daftar Product',
            cols: {
                code: 'Kode',
                group_id: 'Kelompok',
                brand_id: 'Merk',
                name: 'Nama',
                supplier_id: 'Pemasok',
                tax_status: 'PPN',
                remarks: 'Remarks',
                estimated_capital_price: 'Perkiraan Harga Modal',
                point: 'Poin',
                is_use_serial: 'Memakai Nomor Serial',
                product_type: 'Tipe Produk',
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
            delete: 'Hapus',
        },
        fields: {
            code: 'Kode',
            group_id: 'Nama Group',
            brand_id: 'Nama Merk',
            name: 'Nama',
            supplier_id: 'Pemasok',
            tax_status: 'PPN',
            remarks: 'Remarks',
            estimated_capital_price: 'Perkiraan Harga Modal',
            point: 'Poin',
            is_use_serial: 'Memakai Nomor Serial',
            product_type: 'Tipe Produk',
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
        tax_statusDDL: {
            notax: 'Tanpa PPN',
            excudetax: 'Belum Termasuk PPN',
            includetax: 'Termasuk PPN'
        },
        product_typeDDL: {
            rawmaterial: 'RAW Material',
            wip: 'WIP',
            finishedgoods: 'Finished Goods',
            service: 'Service',
        },
        is_use_serial: {
            active: 'Aktif',
            inactive: 'Tidak Aktif',
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
    .use(ZiggyVue, Ziggy)
    .use(i18n)
    .mount('#productVue')
