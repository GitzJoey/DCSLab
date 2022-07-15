import { mount } from "@vue/test-utils";
import { describe, expect, it } from 'vitest';

import i18n from "@/lang";
import VeeValidate from "@/validation";
import { setActivePinia, createPinia } from 'pinia'

import LoadingIcon from "@/global-components/loading-icon/Main.vue";

import Login from "@/views/login/Main.vue";

describe('Login.vue', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
    });

    it('should load properly', () => {
        const wrapper = mount(Login, {
            global: {
                components: {
                    LoadingIcon
                },
                plugins: [
                    i18n,
                    VeeValidate
                ]
            }
        });

        expect(wrapper.text()).contains('Login');
    });
});