import { createI18n } from "vue-i18n"
import * as en from "./en.json"
import * as id from "./id.json"
import { setLocale } from "@vee-validate/i18n";

let language = document.documentElement.lang;

const i18n = createI18n({
    legacy: false,
    locale: language,
    fallbackLocale: 'en',
    messages: {
        en: en,
        id: id
    },
    missingWarn: false,
    fallbackWarn: false
});

export function switchLang(lang) {
    i18n.global.locale.value = lang;
    document.documentElement.setAttribute('lang', lang);
    localStorage.setItem('DCSLAB_LANG', lang);

    setLocale(lang);
}

export function getLang() {
    return i18n.global.locale.value;
}

export default i18n;
