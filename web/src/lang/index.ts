import { createI18n } from "vue-i18n";
import en from "./messages.en";
import id from "./messages.id";
import { setLocale } from "@vee-validate/i18n";
import { Ref } from "vue";

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

export function switchLang(lang: Ref<"en"|"id">) {
    i18n.global.locale.value = lang.value;
    document.documentElement.setAttribute('lang', lang.value);
    localStorage.setItem('DCSLAB_LANG', lang.value);

    setLocale(lang.value);
}

export function getLang(): string {
    return i18n.global.locale.value;
}

export default i18n;
