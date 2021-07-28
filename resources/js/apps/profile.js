import { createApp } from 'vue';
import { createI18n }from 'vue-i18n';
import Profile from './components/Profile';

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common["Accept"] = "application/json"
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.head.querySelector('meta[name="csrf-token"]').content;

let language = document.documentElement.lang;
window.axios.defaults.headers.common['X-localization'] = language;

const messages = {
    en: {
        title: 'Your Profile',
        tabs: {
            profile: 'Profile',
            settings: 'Settings'
        },
        buttons: {
            update: 'Update',
        },
        fields: {
            name: 'Name',
            first_name: 'First Name',
            last_name: 'Last Name',
            address: 'Address',
            city: 'City',
            postal_code: 'Postal Code',
            country: 'Country',
            tax_id: 'Tax ID',
            remarks: 'Remarks',
            email: 'Email',
            ic_num: 'IC No.',
            settings: {
                settings: 'Settings',
                theme: 'Themes',
                dateFormat: 'Date Format',
                timeFormat: 'Time Format',
            },
        },
        errors: {
            warning: 'Warning',
        }
    },
    id: {
        title: 'Profil Kamu',
        tabs: {
            profile: 'Profil',
            settings: 'Settings'
        },
        buttons: {
            update: 'Update',
        },
        fields: {
            name: 'Nama',
            first_name: 'Nama Depan',
            last_name: 'Nama Keluarga',
            address: 'Alamat',
            city: 'Kota',
            postal_code: 'Kode Pos',
            country: 'Negara',
            tax_id: 'NPWP',
            remarks: 'Keterangan',
            email: 'Email',
            ic_num: 'KTP',
            settings: {
                settings: 'Pengaturan',
                theme: 'Tema',
                dateFormat: 'Format Tanggal',
                timeFormat: 'Format Waktu',
            },
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

createApp(Profile)
    .use(i18n)
    .mount('#profileVue')
