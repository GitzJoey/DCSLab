import { createApp } from 'vue';
import { createI18n }from 'vue-i18n';
import FinanceCash from './components/FinanceCash';

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common["Accept"] = "application/json"
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.head.querySelector('meta[name="csrf-token"]').content;

let language = document.documentElement.lang;
window.axios.defaults.headers.common['X-localization'] = language;

const messages = {
    en: {
        table: {
            title: 'Cash Lists',
            cols: {
                code: 'Code',
                name: 'Name',
                status: 'Status',
                bank: 'Is Bank',
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
        },
        fields: {
            code: 'Code',
            name: 'Name',
            status: 'Status',
            bank: 'Is Bank',
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
            title: 'Daftar Kas',
            cols: {
                kode: 'Kode',
                name: 'Nama',
                status: 'Status',
                bank: 'Bank',
            }
        },
        buttons: {
            submit: 'Kirim',
            reset: 'Reset',
            back: 'Kembali',
        },
        actions: {
            create: 'Tambah Kas',
            edit: 'Ubah Kas',
            show: 'Tampilkan Kas',
            reset_password: 'Reset Password',
        },
        fields: {
            code: 'Kode',
            name: 'Nama',
            status: 'Status',
            bank: 'Bank',
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

createApp(FinanceCash)
    .use(i18n)
    .mount('#financecashVue')
