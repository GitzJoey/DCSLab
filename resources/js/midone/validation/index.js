import {
    Form as VeeForm,
    Field as VeeField,
    defineRule,
    ErrorMessage, configure,
} from "vee-validate";
import {
    required,
    min,
    max,
    alpha,
    alpha_spaces as alphaSpaces,
    alpha_dash,
    alpha_num,
    email,
    min_value as minVal,
    max_value as maxVal,
    not_one_of as excluded,
    confirmed,
    numeric,
} from "@vee-validate/rules";

import { localize, setLocale } from '@vee-validate/i18n';
import en from '@vee-validate/i18n/dist/locale/en.json';
import id from '@vee-validate/i18n/dist/locale/id.json';

import { getLang } from '../lang';

configure({
    validateOnInput: true,
    generateMessage: localize({ en, id }),
})

setLocale(getLang());

export default {
    install(app) {
        app.component("VeeForm", VeeForm);
        app.component("VeeField", VeeField);
        app.component("ErrorMessage", ErrorMessage);

        defineRule("required", required);
        defineRule("min", min);
        defineRule("max", max);
        defineRule("alpha", alpha);
        defineRule("alpha_spaces", alphaSpaces);
        defineRule("alpha_dash", alpha_dash);
        defineRule("alpha_num", alpha_num);
        defineRule("email", email);
        defineRule("min_value", minVal);
        defineRule("max_value", maxVal);
        defineRule("excluded", excluded);
        defineRule("country_excluded", excluded);
        defineRule("password_mismatch", confirmed);
        defineRule("numeric", numeric);
    },
};
