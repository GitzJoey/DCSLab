import { createApp } from 'vue';
import { ZiggyVue } from 'ziggy';
import { Ziggy } from '../ziggy';
import { createI18n }from 'vue-i18n';
import Employee from './components/Employee';

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common["Accept"] = "application/json"
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.head.querySelector('meta[name="csrf-token"]').content;

let language = document.documentElement.lang;
window.axios.defaults.headers.common['X-localization'] = language;

const messages = {
    en: {
        table: {
            title: 'Employee Lists',
            cols: {
                company_id: 'Company Name',
                name: 'Name',
                email: 'Email',
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
            create: 'Create Employee',
            edit: 'Edit Employee',
            show: 'Show Employee',
            delete: 'Delete',
        },
        fields: {
            company_id: 'Company Name',
            name: 'Name',
            email: 'Email',
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
        },
    },
    id: {
        table: {
            title: 'Daftar Karyawan',
            cols: {
                company_id: 'Company Name',
                name: 'Nama',
                email: 'Email',
                address: 'Alamat',
                city: 'Kota',
                contact: 'Kontak',
                remarks: 'Remarks',
                status: 'Status',
            }
        },
        buttons: {
            submit: 'Simpan',
            reset: 'Reset',
            back: 'Kembali',
        },
        actions: {
            create: 'Tambah Karyawan',
            edit: 'Ubah Karyawan',
            show: 'Tampilkan Karyawan',
            delete: 'Hapus',
        },
        fields: {
            company_id: 'Company Name',
            name: 'Nama',
            email: 'Email',
            address: 'Alamat',
            city: 'Kota',
            contact: 'Kontak',
            remarks: 'Remarks',
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
        },
    }
};

const i18n = createI18n({
    locale: language,
    fallbackLocale: 'en',
    messages,
});

createApp(Employee)
    .use(ZiggyVue, Ziggy)
    .use(i18n)
    .mount('#employeeVue')
