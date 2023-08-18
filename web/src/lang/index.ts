import { createI18n } from "vue-i18n";
import en from "./messages.en";
import id from "./messages.id";

const language = document.documentElement.lang;

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

export function switchLang(lang: "en" | "id"): void {
    i18n.global.locale.value = lang;
    document.documentElement.setAttribute('lang', lang);
    localStorage.setItem('DCSLAB_LANG', lang);
}

export function getLang(): string {
    return i18n.global.locale.value;
}

export default i18n;
