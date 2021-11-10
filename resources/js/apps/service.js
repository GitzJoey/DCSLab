import { createApp } from 'vue';
import { ZiggyVue } from 'ziggy';
import { Ziggy } from '../ziggy';
import { createI18n }from 'vue-i18n';
import Service from './components/Service';

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common["Accept"] = "application/json"
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.head.querySelector('meta[name="csrf-token"]').content;

let language = document.documentElement.lang;
window.axios.defaults.headers.common['X-localization'] = language;

const messages = {
    en: {
        table: {
            title: 'Service Lists',
            cols: {
                code: 'Code',
                name: 'Name',
                group_id: 'Product Group',
                unit_id: 'Unit',
                tax_status: 'Tax Status',
                remarks: 'Remarks',
                point: 'Point',
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
            create: 'Create Service',
            edit: 'Edit Service',
            show: 'Show Service',
            delete: 'Delete',
        },
        fields: {
            code: 'Code',
            name: 'Name',
            group_id: 'Product Group',
            unit_id: 'Unit',
            tax_status: 'Tax Status',
            remarks: 'Remarks',
            point: 'Point',
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
        statusDDL: {
            active: 'Active',
            inactive: 'Inactive'
        }
    },
    id: {
        table: {
            title: 'Daftar Merk Product',
            cols: {
                code: 'Kode',
                name: 'Nama',
                group_id: 'Produk Grup',
                unit_id: 'Satuan',
                tax_status: 'Status Pajak',
                remarks: 'Remarks',
                point: 'Point',
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
            create: 'Tambah Merk Product',
            edit: 'Ubah Service',
            show: 'Tampilkan Service',
            delete: 'Hapus',
        },
        fields: {
            code: 'Kode',
            name: 'Nama',
            group_id: 'Produk Grup',
            unit_id: 'Satuan',
            tax_status: 'Status Pajak',
            remarks: 'Remarks',
            point: 'Point',
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
        statusDDL: {
            active: 'Aktif',
            inactive: 'Tidak Aktif'
        }
    }
};

const i18n = createI18n({
    locale: language,
    fallbackLocale: 'en',
    messages,
});

createApp(Service)
    .use(ZiggyVue, Ziggy)
    .use(i18n)
    .mount('#serviceVue')
