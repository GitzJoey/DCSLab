import { createApp } from 'vue';
import { ZiggyVue } from 'ziggy';
import { Ziggy } from '../ziggy';
import { createI18n }from 'vue-i18n';
import Warehouse from './components/Warehouse';

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common["Accept"] = "application/json"
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.head.querySelector('meta[name="csrf-token"]').content;

let language = document.documentElement.lang;
window.axios.defaults.headers.common['X-localization'] = language;

const messages = {
    en: {
        table: {
            title: 'Warehouse Lists',
            cols: {
                company_id: 'Company Name',
                code: 'Code',
                name: 'Name',
                address: 'Address',
                city: 'City',
                contact: 'Contact',
                remarks: 'Remarks',
                status: 'Status',
            }
        },
        buttons: {
            submit: 'Submit',
            reset: 'Reset',
            back: 'Back',
        },
        actions: {
            create: 'Create Warehouse',
            edit: 'Edit Warehouse',
            show: 'Show Warehouse',
            delete: 'Delete',
        },
        fields: {
            company_id: 'Company Name',
            code: 'Code',
            name: 'Name',
            address: 'Address',
            city: 'City',
            contact: 'Contact',
            remarks: 'Remarks',
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
        }
    },
    id: {
        table: {
            title: 'Daftar Perusahaan',
            cols: {
                company_id: 'Nama Perusahaan',
                code: 'Kode',
                name: 'Nama',
                address: 'Alamat',
                city: 'Kota',
                contact: 'Kontak',
                remarks: 'Keterangan',
                status: 'Status',
            }
        },
        buttons: {
            submit: 'Simpan',
            reset: 'Reset',
            back: 'Kembali',
        },
        actions: {
            create: 'Tambah Gudang',
            edit: 'Ubah Gudang',
            show: 'Tampilkan Gudang',
            delete: 'Hapus',
        },
        fields: {
            company_id: 'Nama Perusahaan',
            code: 'Kode',
            name: 'Nama',
            address: 'Alamat',
            city: 'Kota',
            contact: 'Kontak',
            remarks: 'Keterangan',
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
        }
    }
};

const i18n = createI18n({
    locale: language,
    fallbackLocale: 'en',
    messages,
});

createApp(Warehouse)
    .use(ZiggyVue, Ziggy)
    .use(i18n)
    .mount('#warehouseVue')
