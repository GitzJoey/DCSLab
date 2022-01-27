import { createApp } from 'vue';
import { ZiggyVue } from 'ziggy';
import { Ziggy } from '../ziggy';
import { createI18n }from 'vue-i18n';
import Cash from './components/Cash';

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common["Accept"] = "application/json"
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.head.querySelector('meta[name="csrf-token"]').content;

let language = document.documentElement.lang;
window.axios.defaults.headers.common['X-localization'] = language;

const messages = {
    en: {
        table: {
            title: 'Cash & Bank Lists',
            cols: {
                code: 'Code',
                name: 'Name',
                is_bank: 'Is Bank',
                status: 'Status',
            }
        },
        buttons: {
            submit: 'Submit',
            reset: 'Reset',
            back: 'Back',
        },
        actions: {
            create: 'Create Cash',
            edit: 'Edit Cash',
            show: 'Show Cash',
            delete: 'Delete',
        },
        fields: {
            code: 'Code',
            name: 'Name',
            is_bank: 'Is Bank',
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
            data_not_found: 'Data Not Found',
            please_select: 'Please Select',
        },
        is_bank: {
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
            title: 'Daftar Kas & Bank',
            cols: {
                code: 'Kode',
                name: 'Nama',
                is_bank: 'Bank',
                status: 'Status',
            }
        },
        buttons: {
            submit: 'Simpan',
            reset: 'Reset',
            back: 'Kembali',
        },
        actions: {
            create: 'Tambah Kas',
            edit: 'Ubah Kas',
            show: 'Tampilkan Kas',
            delete: 'Hapus',
        },
        fields: {
            code: 'Kode',
            name: 'Nama',
            is_bank: 'Bank',
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
            data_not_found: 'Data Tidak Ditemukan',
            please_select: 'Silahkan Pilih',
        },
        is_bank: {
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

createApp(Cash)
    .use(ZiggyVue, Ziggy)
    .use(i18n)
    .mount('#cashVue')
