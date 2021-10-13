import { createI18n } from 'vue-i18n'
import * as en from './en.json'
import * as id from './id.json'

let language = document.documentElement.lang;

const i18n = createI18n({
    legacy: false,
    locale: language,
    fallbackLocale: 'en',
    messages: {
        en: en,
        id: id
    }
});

export default i18n;
