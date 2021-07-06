import { createApp } from 'vue';
import { createI18n }from 'vue-i18n';
import House from './components/House';

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common["Accept"] = "application/json"
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.head.querySelector('meta[name="csrf-token"]').content;

let language = document.documentElement.lang;
window.axios.defaults.headers.common['X-localization'] = language;

const messages = {
    en: {
        table: {
            title: 'House Lists',
            cols: {
                code: 'Code',
                name: 'Name',
                status: 'Status',
            }
        },
        buttons: {
            submit: 'Submit',
            reset: 'Reset',
            back: 'Back',
        },
        actions: {
            create: 'Create House',
            edit: 'Edit House',
            show: 'Show House',
        },
        fields: {
            code: 'Code',
            name: 'Name',
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
            title: 'Daftar Rumah',
            cols: {
                kode: 'Kode',
                name: 'Nama',
                status: 'Status',
            }
        },
        buttons: {
            submit: 'Simpan',
            reset: 'Reset',
            back: 'Kembali',
        },
        actions: {
            create: 'Tambah Rumah',
            edit: 'Ubah Rumah',
            show: 'Tampilkan Rumah',
            reset_password: 'Reset Password',
        },
        fields: {
            code: 'Kode',
            name: 'Nama',
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

createApp(House)
    .use(i18n)
    .mount('#houseVue')
