import { createApp } from 'vue';
import { createI18n } from 'vue-i18n';
import Inbox from './components/Inbox';

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common["Accept"] = "application/json"
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.head.querySelector('meta[name="csrf-token"]').content;

let language = document.documentElement.lang;
window.axios.defaults.headers.common['X-localization'] = language;

const messages = {
    en: {
        table: {
            title: 'Inbox',
        },
        buttons: {
            submit: 'Submit',
            reset: 'Reset',
            back: 'Back',
        },
        actions: {
            create: 'Compose',
        },
        fields: {
            to: 'To',
            subject: 'Subject',
            message: 'Message',
        },
        errors: {
            warning: 'Warning',
        },
        placeholder: {
            addnew: "Add New",
            removing: 'Removing',
        }
    },
    id: {
        table: {
            title: 'Kotak Masuk',
        },
        buttons: {
            submit: 'Kirim',
            reset: 'Reset',
            back: 'Kembali',
        },
        actions: {
            create: 'Surat Baru',
        },
        fields: {
            to: 'Kepada',
            subject: 'Subjek',
            message: 'Pesan',
        },
        errors: {
            warning: 'Peringatan',
        },
        placeholder: {
            addnew: 'Tambah Baru',
            removing: 'Menghapus',
        }
    }
};

const i18n = createI18n({
    locale: language,
    fallbackLocale: 'en',
    messages,
});

createApp(Inbox)
    .use(i18n)
    .mount('#inboxVue')
