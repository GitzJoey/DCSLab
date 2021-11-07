import { createApp } from 'vue';
import { ZiggyVue } from 'ziggy';
import { Ziggy } from '../ziggy';
import { createI18n }from 'vue-i18n';
import ProductGroup from './components/ProductGroup';

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common["Accept"] = "application/json"
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.head.querySelector('meta[name="csrf-token"]').content;

let language = document.documentElement.lang;
window.axios.defaults.headers.common['X-localization'] = language;

const messages = {
    en: {
        table: {
            title: 'Product Group Lists',
            cols: {
                code: 'Code',
                name: 'Name',
                category: 'Category',
            }
        },
        buttons: {
            submit: 'Submit',
            reset: 'Reset',
            back: 'Back',
        },
        actions: {
            create: 'Create Product Group',
            edit: 'Edit Product Group',
            show: 'Show Product Group',
            delete: 'Delete',
        },
        fields: {
            code: 'Code',
            name: 'Name',
            category: 'Category',
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
        categoryDDL: {
            product: 'Product',
            service: 'Service',
            productandservice: 'Product and Service'
        },
    },
    id: {
        table: {
            title: 'Daftar Product Group',
            cols: {
                code: 'Kode',
                name: 'Nama',
                category: 'Kategory',
            }
        },
        buttons: {
            submit: 'Simpan',
            reset: 'Reset',
            back: 'Kembali',
        },
        actions: {
            create: 'Tambah Product Group',
            edit: 'Ubah Product Group',
            show: 'Tampilkan Product Group',
            delete: 'Hapus',
        },
        fields: {
            code: 'Kode',
            name: 'Nama',
            category: 'Kategory',
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
        categoryDDL: {
            product: 'Barang',
            service: 'Jasa',
            productandservice: 'Barang and Jasa'
        },
    }
};

const i18n = createI18n({
    locale: language,
    fallbackLocale: 'en',
    messages,
});

createApp(ProductGroup)
    .use(ZiggyVue, Ziggy)
    .use(i18n)
    .mount('#productgroupVue')
