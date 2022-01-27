import { createApp } from 'vue';
import { ZiggyVue } from 'ziggy';
import { Ziggy } from '../ziggy';
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
                is_member: 'Member Card',
                customer_group_id: 'Customer Group',
                zone: 'Zone',
                max_open_invoice: 'Max Open Invoice',
                max_outstanding_invoice: 'Max Outstanding Invoice',
                max_invoice_age: 'Max Invoice Age',
                payment_term: 'Payment Term',
                address: 'Address',
                city: 'City',
                contact: 'Contact',
                tax_id: 'Tax ID',
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
            create: 'Create Customer',
            edit: 'Edit Customer',
            show: 'Show Customer',
            delete: 'Delete',
        },
        fields: {
            code: 'Code',
            name: 'Name',
            is_member: 'Member Card',
            customer_group_id: 'Customer Group',
            zone: 'Zone',
            max_open_invoice: 'Max Open Invoice',
            max_outstanding_invoice: 'Max Outstanding Invoice',
            max_invoice_age: 'Max Invoice Age',
            payment_term: 'Payment Term',
            address: 'Address',
            city: 'City',
            contact: 'Contact',
            tax_id: 'Tax ID',
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
            data_not_found: 'Data Not Found',
            please_select: 'Please Select',
        },
        is_member: {
            active: 'Yes',
            inactive: 'No',
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
                is_member: 'Kartu Member',
                customer_group_id: 'Grup Pelanggan',
                zone: 'Wilayah',
                max_open_invoice: 'Batas Nota',
                max_outstanding_invoice: 'Batas Nominal',
                max_invoice_age: 'Batas Umur Nota',
                payment_term: 'Tempo',
                address: 'Alamat',
                city: 'Kota',
                contact: 'Kontak',
                tax_id: 'Nomor Pajak',
                remarks: 'Keterangan',
                status: 'Status',
            }
        },
        buttons: {
            submit: 'Simpan',
            reset: 'Reset',
            back: 'Kembali',
        },
        actions: {
            create: 'Tambah Pelanggan',
            edit: 'Ubah Pelanggan',
            show: 'Tampilkan Pelanggan',
            delete: 'Hapus',
        },
        fields: {
            code: 'Kode',
            name: 'Name',
            is_member: 'Kartu Member',
            customer_group_id: 'Grup Pelanggan',
            zone: 'Wilayah',
            max_open_invoice: 'Jumlah Nota Maks',
            max_outstanding_invoice: 'Nominal Nota Maks',
            max_invoice_age: 'Umur Nota Makss',
            payment_term: 'Tempo',
            address: 'Alamat',
            city: 'Kota',
            contact: 'Kontak',
            tax_id: 'Nomor Pajak',
            remarks: 'Keterangan',
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
            data_not_found: 'Data Tidak Ditemukan',
            please_select: 'Silahkan Pilih',
        },
        is_member: {
            active: 'Ya',
            inactive: 'Tidak',
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
    .use(ZiggyVue, Ziggy)
    .use(i18n)
    .mount('#customerVue')
