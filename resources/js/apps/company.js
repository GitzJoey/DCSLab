import { createApp } from 'vue';
import { createI18n } from 'vue-i18n';
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
            title: 'User Lists',
            cols: {
                name: 'Name',
                email: 'Email',
                roles: 'Roles',
                company_name: 'Company',
            }
        },
        buttons: {
            submit: 'Submit',
            reset: 'Reset',
            back: 'Back',
        },
        actions: {
            create: 'Create User',
            edit: 'Edit User',
            show: 'Show User',
            reset_password: 'Reset Password',
        },
        fields: {
            name: 'Name',
            email: 'Email',
            roles: 'Roles',
            first_name: 'First Name',
            last_name: 'Last Name',
            address: 'Address',
            ic_num: 'IC Number',
            company_name: 'Company Name',
            country: 'Country',
            remarks: 'Remarks',
            status: 'Status',
            tax_id: 'Tax ID',
            postal_code: 'Postal Code',
            city: 'City',
            settings: {
                settings: 'Settings',
                theme: 'Themes',
                dateFormat: 'Date Format',
                timeFormat: 'Time Format',
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
            title: 'Daftar Pengguna',
            cols: {
                name: 'Nama',
                email: 'Email',
                roles: 'Peran',
                company_name: 'Perusahaan',
            }
        },
        buttons: {
            submit: 'Kirim',
            reset: 'Reset',
            back: 'Kembali',
        },
        actions: {
            create: 'Tambah Pengguna',
            edit: 'Ubah Pengguna',
            show: 'Tampilkan Pengguna',
            reset_password: 'Reset Password',
        },
        fields: {
            name: 'Nama',
            email: 'Email',
            roles: 'Peran',
            first_name: 'Nama Depan',
            last_name: 'Nama Belakang',
            address: 'Alamat',
            ic_num: 'Nomor KTP',
            company_name: 'Nama Perusahaan',
            country: 'Negara',
            remarks: 'Deskripsi',
            status: 'Status',
            tax_id: 'No NPWP',
            postal_code: 'Kode Pos',
            city: 'Kota',
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

createApp(Company)
    .use(i18n)
    .mount('#companyVue')
