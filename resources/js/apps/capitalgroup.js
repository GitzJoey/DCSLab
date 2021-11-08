import { createApp } from 'vue';
import { ZiggyVue } from 'ziggy';
import { Ziggy } from '../ziggy';
import { createI18n }from 'vue-i18n';
import CapitalGroup from './components/CapitalGroup';

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common["Accept"] = "application/json"
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.head.querySelector('meta[name="csrf-token"]').content;

let language = document.documentElement.lang;
window.axios.defaults.headers.common['X-localization'] = language;

const messages = {
    en: {
        table: {
            title: 'Capital Group Lists',
            cols: {
                company_id: 'Company Name',
                code: 'Code',
                name: 'Name',
            }
        },
        buttons: {
            submit: 'Submit',
            reset: 'Reset',
            back: 'Back',
        },
        actions: {
            create: 'Create Capital Group',
            edit: 'Edit Capital Group',
            show: 'Show Capital Group',
            delete: 'Delete',
        },
        fields: {
            company_id: 'Company Name',
            code: 'Code',
            name: 'Name',
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
    },
    id: {
        table: {
            title: 'Daftar Capital Group',
            cols: {
                company_id: 'Nama Perusahaan',
                code: 'Kode',
                name: 'Nama',
            }
        },
        buttons: {
            submit: 'Simpan',
            reset: 'Reset',
            back: 'Kembali',
        },
        actions: {
            create: 'Tambah Capital Group',
            edit: 'Ubah Capital Group',
            show: 'Tampilkan Capital Group',
            delete: 'Hapus',
        },
        fields: {
            code: 'Kode',
            name: 'Nama',
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
    }
};

const i18n = createI18n({
    locale: language,
    fallbackLocale: 'en',
    messages,
});

createApp(CapitalGroup)
    .use(ZiggyVue, Ziggy)
    .use(i18n)
    .mount('#capitalgroupVue')
