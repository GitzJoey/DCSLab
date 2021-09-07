import { createApp } from 'vue';
import { createI18n }from 'vue-i18n';
import Company from './components/Company';

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common["Accept"] = "application/json"
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.head.querySelector('meta[name="csrf-token"]').content;

let language = document.documentElement.lang;
window.axios.defaults.headers.common['X-localization'] = language;

const messages = {
    en: {
        table: {
            title: 'Company Lists',
            cols: {
                code: 'Code',
                name: 'Name',
                default: 'Default',
                status: 'Status',
            }
        },
        buttons: {
            submit: 'Submit',
            reset: 'Reset',
            back: 'Back',
        },
        actions: {
            create: 'Create Company',
            edit: 'Edit Company',
            delete: 'Delete',
        },
        fields: {
            code: 'Code',
            name: 'Name',
            default: 'Default',
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
        defaultDDL: {
            active: 'Active',
            inactive: 'Inactive',
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
                code: 'Kode',
                name: 'Nama',
                default: 'Default',
                status: 'Status',
            }
        },
        buttons: {
            submit: 'Simpan',
            reset: 'Reset',
            back: 'Kembali',
        },
        actions: {
            create: 'Tambah Perusahaan',
            edit: 'Ubah Perusahaan',
            show: 'Tampilkan Perusahaan',
            delete: 'Hapus',
        },
        fields: {
            code: 'Kode',
            name: 'Nama',
            default: 'Default',
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
        defaultDDL: {
            active: 'Aktif',
            inactive: 'Tidak Aktif',
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

createApp(Company)
    .use(i18n)
    .mount('#companyVue')
