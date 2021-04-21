import { createApp } from 'vue';
import { createI18n }from 'vue-i18n';
import Profile from './components/Profile';

let lang = document.documentElement.lang;

const messages = {
    en: {
        buttons: {
            update: 'Update',
        },
        fields: {
        },
        errors: {
            warning: 'Warning',
        }
    },
    id: {
        buttons: {
            update: 'Update',
        },
        fields: {
        },
        errors: {
            warning: 'Peringatan',
        }
    }
};

const i18n = createI18n({
    locale: lang,
    fallbackLocale: 'en',
    messages,
});

createApp(Profile)
    .use(i18n)
    .mount('#roleVue')
