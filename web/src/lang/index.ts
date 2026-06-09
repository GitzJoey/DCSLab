import { createI18n } from "vue-i18n"
import en from "./messages.en"
import id from "./messages.id"

const language = document.documentElement.lang

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
})

export default i18n