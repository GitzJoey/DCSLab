import { createApp } from 'vue';
import { createI18n }from 'vue-i18n';
import Branch from './components/Branch';

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common["Accept"] = "application/json"
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.head.querySelector('meta[name="csrf-token"]').content;

let language = document.documentElement.lang;
window.axios.defaults.headers.common['X-localization'] = language;

const messages = {
    en: {
        table: {
            title: 'Branch Lists',
            cols: {
                company_name: 'Company Name',
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
            create: 'Create Branch',
            edit: 'Edit Branch',
            show: 'Show Branch',
        },
        fields: {
            company_name: 'Company Name',
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
                company_name: 'Nama Perusahaan',
                kode: 'Kode',
                name: 'Nama',
                address: 'Alamat',
                city: 'Kota',
                contact: 'Kontak',
                remarks: 'Deskripsi',
                status: 'Status',
            }
        },
        buttons: {
            submit: 'Kirim',
            reset: 'Reset',
            back: 'Kembali',
        },
        actions: {
            create: 'Tambah cabang',
            edit: 'Ubah Cabang',
            show: 'Tampilkan Cabang',
            reset_password: 'Reset Password',
        },
        fields: {
            company_name: 'Nama Perusahaan',
            kode: 'Kode',
            name: 'Nama',
            address: 'Alamat',
            city: 'Kota',
            contact: 'Kontak',
            remarks: 'Deskripsi',
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

createApp(Branch)
    .use(i18n)
    .mount('#branchVue')
