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

export function switchLang(lang) {
    i18n.global.locale.value = lang;
    document.documentElement.setAttribute('lang', lang);
    localStorage.setItem('DSCSLAB_LANG', lang);
}

export function getLang() {
    return i18n.global.locale.value;
}

export default i18n;
