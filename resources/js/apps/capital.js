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
                ref_number: 'Ref Number',
                investor: 'Investor',
                capital_group: 'Capital Group',
                cash_id: 'Cash',
                date: 'Date',
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
            ref_number: 'Ref Number',
            investor: 'Investor',
            capital_group: 'Capital Group',
            cash_id: 'Cash',
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
    },
    id: {
        table: {
            title: 'Daftar Capital',
            cols: {
                ref_number: 'Ref Number',
                investor: 'Investor',
                capital_group: 'Modal Grup',
                cash_id: 'Kas',
                date: 'Tanggal',
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
            ref_number: 'Ref Number',
            investor: 'Investor',
            capital_group: 'Modal Grup',
            cash_id: 'Kas',
            date: 'Tanggal',
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
