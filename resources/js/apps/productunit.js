import { createApp } from 'vue';
import { createI18n }from 'vue-i18n';
import ProductUnit from './components/ProductUnit';

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common["Accept"] = "application/json"
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.head.querySelector('meta[name="csrf-token"]').content;

let language = document.documentElement.lang;
window.axios.defaults.headers.common['X-localization'] = language;

const messages = {
    en: {
        table: {
            title: 'Product Unit Lists',
            cols: {
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
            create: 'Create Product Unit',
            edit: 'Edit Product Group',
            show: 'Show Product Group',
        },
        fields: {
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
            title: 'Daftar Satuan Product',
            cols: {
                kode: 'Kode',
                name: 'Nama',
            }
        },
        buttons: {
            submit: 'Kirim',
            reset: 'Reset',
            back: 'Kembali',
        },
        actions: {
            create: 'Tambah Satuan Product',
            edit: 'Ubah Product Group',
            show: 'Tampilkan Product Group',
            reset_password: 'Reset Password',
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

createApp(ProductUnit)
    .use(i18n)
    .mount('#productunitVue')
