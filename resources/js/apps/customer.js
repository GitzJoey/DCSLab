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
                sales_customer_group_id: 'Customer Group',
                sales_territory: 'Territory',
                limit_outstanding_notes: 'Limit Outstanding Notes',
                limit_payable_nominal: 'Limit Payable Nominal',
                limit_age_notes: 'Limit Age Notes',
                term: 'Term',
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
            sales_customer_group_id: 'Customer Group',
            sales_territory: 'Territory',
            use_limit_outstanding_notes: 'Use Limit Outstanding Notes',
            limit_outstanding_notes: 'Limit Outstanding Notes',
            use_limit_payable_nominal: 'Use Limit Payable Nominal',
            limit_payable_nominal: 'Limit Payable Nominal',
            use_limit_age_notes: 'Use Age Notes',
            limit_age_notes: 'Limit Age Notes',
            term: 'Term',
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
            please_select: 'Please Select',
        },
        use_limit_outstanding_notes: {
            active: 'Yes',
            inactive: 'No',
        },
        use_limit_payable_nominal: {
            active: 'Yes',
            inactive: 'No',
        },
        use_limit_age_notes: {
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
                sales_customer_group_id: 'Grup Pelanggan',
                sales_territory: 'Wilayah',
                limit_outstanding_notes: 'Batas Nota',
                limit_payable_nominal: 'Batas Nominal',
                limit_age_notes: 'Batas Umur Nota',
                term: 'Tempo',
                address: 'Alamat',
                city: 'Kota',
                contact: 'Kontak',
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
            create: 'Tambah Pelanggan',
            edit: 'Ubah Pelanggan',
            show: 'Tampilkan Pelanggan',
            delete: 'Hapus',
        },
        fields: {
            code: 'Kode',
            name: 'Name',
            sales_customer_group_id: 'Grup Pelanggan',
            sales_territory: 'Wilayah',
            use_limit_outstanding_notes: 'Menggunakan Batas Nota',
            limit_outstanding_notes: 'Batas Nota',
            use_limit_payable_nominal: 'Menggunakan Batas Nominal',
            limit_payable_nominal: 'Batas Nominal',
            use_limit_age_notes: 'Menggunakan Batas Hari',
            limit_age_notes: 'Batas Hari',
            term: 'Tempo',
            address: 'Alamat',
            city: 'Kota',
            contact: 'Kontak',
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
        use_limit_outstanding_notes: {
            active: 'Ya',
            inactive: 'Tidak',
        },
        use_limit_payable_nominal: {
            active: 'Ya',
            inactive: 'Tidak',
        },
        use_limit_age_notes: {
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
    .use(i18n)
    .mount('#customerVue')
