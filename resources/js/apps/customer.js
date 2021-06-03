import { createApp } from 'vue';
import { createI18n }from 'vue-i18n';
import Customer from './components/Customer';

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common["Accept"] = "application/json"
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.head.querySelector('meta[name="csrf-token"]').content;

let language = document.documentElement.lang;
window.axios.defaults.headers.common['X-localization'] = language;

const messages = {
    en: {
        table: {
            title: 'Customer Lists',
            cols: {
                code: 'Code',
                name: 'Name',
                sales_customer_group: 'Sales Customer Group',
                sales_territory: 'Sales Territory',
                limit_outstanding_notes: 'Limit Outstanding Notes',
                limit_payable_nominal: 'Limit Payable Nominal',
                limit_due_date: 'Limit Due Date',
                term: 'Term',
                address: 'Address',
                city: 'City',
                contact: 'Contact',
                tax_id: 'Tax ID',
                status: 'Status',
            }
        },
        buttons: {
            submit: 'Submit',
            reset: 'Reset',
            back: 'Back',
        },
        actions: {
            create: 'Create Customer',
            edit: 'Edit Customer',
            show: 'Show Customer',
        },
        fields: {
            code: 'Code',
            name: 'Name',
            sales_customer_group: 'Sales Customer',
            sales_territory: 'Sales Territory',
            use_limit_outstanding_notes: 'Use Limit Outstanding Notes',
            limit_outstanding_notes: 'Limit Outstanding Notes',
            use_limit_payable_nominal: 'Use Limit Payable Nominal',
            limit_payable_nominal: 'Limit Payable Nominal',
            use_limit_due_date: 'Use Due Date',
            limit_due_date: 'Limit Due Date',
            term: 'Term',
            address: 'Address',
            city: 'City',
            contact: 'Contact',
            tax_id: 'Tax ID',
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
            title: 'Daftar Kelompok Pelanggan',
            cols: {
                code: 'Kode',
                name: 'Nama',
                sales_customer_group: 'Grup Pelanggan',
                sales_territory: 'Wilayah',
                limit_outstanding_notes: 'Batas Nota',
                limit_payable_nominal: 'Batas Nominal',
                limit_due_date: 'Batas Hari',
                term: 'Tempo',
                address: 'Alamat',
                city: 'Kota',
                contact: 'Kontak',
                tax_id: 'Nomor Pajak',
                status: 'Status',
            }
        },
        buttons: {
            submit: 'Kirim',
            reset: 'Reset',
            back: 'Kembali',
        },
        actions: {
            create: 'Tambah Pelanggan',
            edit: 'Ubah Pelanggan',
            show: 'Tampilkan Pelanggan',
            reset_password: 'Reset Password',
        },
        fields: {
            code: 'Kode',
            name: 'Name',
            sales_customer_group: 'Grup Pelanggan',
            sales_territory: 'Wilayah',
            use_limit_outstanding_notes: 'Menggunakan Batas Nota',
            limit_outstanding_notes: 'Batas Nota',
            use_limit_payable_nominal: 'Menggunakan Batas Nominal',
            limit_payable_nominal: 'Batas Nominal',
            use_limit_due_date: 'Menggunakan Batas Hari',
            limit_due_date: 'Batas Hari',
            term: 'Tempo',
            address: 'Alamat',
            city: 'Kota',
            contact: 'Kontak',
            tax_id: 'Nomor Pajak',
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

createApp(Customer)
    .use(i18n)
    .mount('#customerVue')
