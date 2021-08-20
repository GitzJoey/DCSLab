import { createApp } from 'vue';
import { ZiggyVue } from 'ziggy';
import { Ziggy } from '../ziggy';
import { createI18n }from 'vue-i18n';
import Role from './components/Role';

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common["Accept"] = "application/json"
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.head.querySelector('meta[name="csrf-token"]').content;

let language = document.documentElement.lang;
window.axios.defaults.headers.common['X-localization'] = language;

const messages = {
    en: {
        table: {
            title: 'Role Lists',
            cols: {
                name: 'Name',
                display_name: 'Display Name',
                description: 'Description',
            }
        },
        buttons: {
            submit: 'Submit',
            reset: 'Reset',
            back: 'Back',
        },
        actions: {
            create: 'Create Role',
            edit: 'Edit Role',
            show: 'Show Role',
        },
        fields: {
            name: 'Name',
            display_name: 'Display Name',
            description: 'Description',
            permissions: 'Permissions',
        },
        errors: {
            warning: 'Warning',
        }
    },
    id: {
        table: {
            title: 'Daftar Peran',
            cols: {
                name: 'Nama',
                display_name: 'Tampilan',
                description: 'Deskripsi',
            }
        },
        buttons: {
            submit: 'Kirim',
            reset: 'Reset',
            back: 'Kembali',
        },
        actions: {
            create: 'Tambah Peran',
            edit: 'Ubah Peran',
            show: 'Tampilkan Peran',
        },
        fields: {
            name: 'Nama',
            display_name: 'Nama Tampilan',
            description: 'Deskripsi',
            permissions: 'Izin',
        },
        errors: {
            warning: 'Peringatan',
        }
    }
};

const i18n = createI18n({
    locale: language,
    fallbackLocale: 'en',
    messages,
});

createApp(Role)
    .use(ZiggyVue, Ziggy)
    .use(i18n)
    .mount('#roleVue')
