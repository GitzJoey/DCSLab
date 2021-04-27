import { createApp } from 'vue';
import { createI18n }from 'vue-i18n';
import User from './components/User';

let lang = document.documentElement.lang;

const messages = {
    en: {
        table: {
            title: 'User Lists',
            cols: {
                name: 'Name',
                email: 'Email',
                roles: 'Roles',
                banned: 'Banned',
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
            ban: 'Ban User',
            unban: 'Unban User',
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
                banned: 'Terlarang',
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
            ban: 'Larang Pengguna',
            unban: 'Batalkan Larangan',
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
    locale: lang,
    fallbackLocale: 'en',
    messages,
});

createApp(User)
    .use(i18n)
    .mount('#userVue')
