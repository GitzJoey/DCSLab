import { createApp } from 'vue';
import { ZiggyVue } from 'ziggy';
import { Ziggy } from '../ziggy';
import { createI18n }from 'vue-i18n';
import Supplier from './components/Supplier';

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common["Accept"] = "application/json"
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.head.querySelector('meta[name="csrf-token"]').content;

let language = document.documentElement.lang;
window.axios.defaults.headers.common['X-localization'] = language;

const messages = {
    en: {
        table: {
            title: 'Purchase Suppliers',
            cols: {
                code: 'Code',
                name: 'Name',
                payment_term_type: 'Payment Term Type',
                contact: 'Contact',
                address: 'Address',
                city: 'City',
                taxable_enterprise: 'Taxable Enterprise',
                tax_id: 'Tax Number',
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
            create: 'Create Supplier',
            edit: 'Edit Supplier',
            show: 'Show Supplier',
            delete: 'Delete',
        },
        fields: {
            code: 'Code',
            name: 'Name',
            payment_term_type: 'Payment Term Type',
            contact: 'Contact',
            address: 'Address',
            city: 'City',
            taxable_enterprise: 'Taxable Enterprise',
            tax_id: 'Tax Number',
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
        taxable_enterprise: {
            active: 'Yes',
            inactive: 'No',
        },
        statusDDL: {
            active: 'Active',
            inactive: 'Inactive',
        },
    },
    id: {
        table: {
            title: 'Pemasok',
            cols: {
                code: 'Kode',
                name: 'Nama',
                payment_term_type: 'Payment Term Type',
                contact: 'Kontak',
                address: 'Alamat',
                city: 'Kota',
                taxable_enterprise: 'Ppn',
                tax_id: 'Nomor Pajak',
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
            create: 'Tambah Kas',
            edit: 'Ubah Kas',
            show: 'Tampilkan Kas',
            delete: 'Hapus',
        },
        fields: {
            code: 'Kode',
            name: 'Nama',
            payment_term_type: 'Payment Term Type',
            contact: 'Kontak',
            address: 'Alamat',
            city: 'Kota',
            taxable_enterprise: 'Ppn',
            tax_id: 'Nomor Pajak',
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
        taxable_enterprise: {
            active: 'Ya',
            inactive: 'Tidak',
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

createApp(Supplier)
    .use(ZiggyVue, Ziggy)
    .use(i18n)
    .mount('#supplierVue')
