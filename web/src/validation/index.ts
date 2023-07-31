import { App } from "vue";
import {
    Form,
    Field,
    FieldArray,
    ErrorMessage,
    defineRule,
    configure,
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

const excludedRules = [
    'regex', 'size', 'url', 'one_of', 'not_one_of', 'mimes', 'image'
];

export default {
    install: (app: App): void => {
        app.component("VeeForm", Form);
        app.component("VeeField", Field);
        app.component("VeeErrorMessage", ErrorMessage);
        app.component("VeeFieldArray", FieldArray)

        Object.keys(allRules).forEach(rule => {
            if (!excludedRules.includes(rule)) {
                defineRule(rule, allRules[rule]);
            }
        });
    },
};
