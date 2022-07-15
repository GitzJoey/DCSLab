import { createApp } from "vue";
import { mount } from "@vue/test-utils";
import { beforeEach, describe, expect, it } from 'vitest';

import i18n from "@/lang";
import VeeValidate from "@/validation";
import { setActivePinia, createPinia } from 'pinia'
import { useDarkModeStore } from "@/stores/dark-mode";

import LoadingIcon from "@/global-components/loading-icon/Main.vue";
import DarkModeSwitcher from "@/components/dark-mode-switcher/Main.vue";

import Login from "@/views/login/Main.vue";

describe('Login.vue', () => {
    const app = createApp({});

    beforeEach(() => {
        const pinia = createPinia().use(useDarkModeStore);
        app.use(pinia);
        setActivePinia(pinia);
    });

    it('can pickup the dark mode switcher value from pinia', () => {
        const darkMode = useDarkModeStore();

        expect(darkMode.value).toBe(false);
    });

    it('should load properly', () => {
        const wrapper = mount(Login, {
            global: {
                components: {
                    DarkModeSwitcher,
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