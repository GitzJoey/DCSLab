import { App } from "vue";
import {
    Form as VeeForm,
    Field as VeeField,
    defineRule,
    ErrorMessage, configure,
} from "vee-validate";
import allRules from "@vee-validate/rules";

import { localize, setLocale } from "@vee-validate/i18n";
import en from "@vee-validate/i18n/dist/locale/en.json";
import id from "@vee-validate/i18n/dist/locale/id.json";

import { getLang } from '../lang';

configure({
    validateOnInput: true,
    generateMessage: localize({ en, id }),
})

setLocale(getLang());

export default {
    install: (app: App): void => {
        app.component("VeeForm", VeeForm);
        app.component("VeeField", VeeField);
        app.component("VeeErrorMessage", ErrorMessage);

        Object.keys(allRules).forEach(rule => {
            defineRule(rule, allRules[rule]);
        });
    },
};
