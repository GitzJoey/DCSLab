import { createApp } from 'vue';
import { ZiggyVue } from 'ziggy';
import { Ziggy } from '../ziggy';
import { createI18n }from 'vue-i18n';
import Capital from './components/Capital';

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common["Accept"] = "application/json"
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.head.querySelector('meta[name="csrf-token"]').content;

let language = document.documentElement.lang;
window.axios.defaults.headers.common['X-localization'] = language;

const messages = {
    en: {
        table: {
            title: 'Capital Lists',
            cols: {
                company_id: 'Company Name',
                ref_number: 'Ref Number',
                investor: 'Investor',
                capital_group: 'Capital Group',
                cash_id: 'Cash',
                date: 'Date',
                capital_status: 'Capital Status',
                amount: 'Amount',
                remarks: 'Remarks',
            }
        },
        buttons: {
            submit: 'Submit',
            reset: 'Reset',
            back: 'Back',
        },
        actions: {
            create: 'Create Capital',
            edit: 'Edit Capital',
            show: 'Show Capital',
            delete: 'Delete',
        },
        fields: {
            company_id: 'Company Name',
            ref_number: 'Ref Number',
            investor: 'Investor',
            capital_group: 'Capital Group',
            cash_id: 'Cash',
            date: 'Date',
            capital_status: 'Capital Status',
            amount: 'Amount',
            remarks: 'Remarks',
            date: {
                date: 'Date',
                dateFormat: 'Date Format',
                timeFormat: 'Time Format'
            },
            amount: 'Amount',
            remarks: 'Remarks',
        },
        errors: {
            warning: 'Warning',
        },
        placeholder: {
            please_select: 'Please Select',
        },
        capital_statusDDL: {
            active: 'In',
            inactive: 'Out',
        },
    },
    id: {
        table: {
            title: 'Daftar Capital',
            cols: {
                company_id: 'Nama Perusahaan',
                ref_number: 'Ref Number',
                investor: 'Investor',
                capital_group: 'Modal Grup',
                cash_id: 'Kas',
                date: 'Tanggal',
                capital_status: 'Status Modal',
                amount: 'Nominal',
                remarks: 'Remarks',
            }
        },
        buttons: {
            submit: 'Simpan',
            reset: 'Reset',
            back: 'Kembali',
        },
        actions: {
            create: 'Tambah Modal',
            edit: 'Ubah Modal',
            show: 'Tampilkan Modal',
            delete: 'Hapus',
        },
        fields: {
            company_id: 'Nama Perusahaan',
            ref_number: 'Ref Number',
            investor: 'Investor',
            capital_group: 'Modal Grup',
            cash_id: 'Kas',
            date: 'Tanggal',
            capital_status: 'Status Modal',
            amount: 'Nominal',
            remarks: 'Remarks',
            date: {
                date: 'Tanggal',
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
        capital_statusDDL: {
            active: 'Masuk',
            inactive: 'Keluar',
        },
    }
};

const i18n = createI18n({
    locale: language,
    fallbackLocale: 'en',
    messages,
});

createApp(Capital)
    .use(ZiggyVue, Ziggy)
    .use(i18n)
    .mount('#capitalVue')
