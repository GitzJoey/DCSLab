import { createApp } from 'vue';
import { createI18n }from 'vue-i18n';
import Role from './components/Role';

let lang = document.documentElement.lang;

const messages = {
    en: {
        table: {
            title: 'Role Lists',
            cols: {
                name: 'Name',
                display_name: 'Display Name',
                description: 'Description',
            }
        }
    },
    id: {
        table: {
            title: 'Daftar Peran',
            cols: {
                name: 'Nama',
                display_name: 'Tampilan',
                description: 'Deskripsi',
            }
        }
    }
};

const i18n = createI18n({
    locale: lang,
    fallbackLocale: 'en',
    messages,
});

createApp(Role)
    .use(i18n)
    .mount('#roleVue')
