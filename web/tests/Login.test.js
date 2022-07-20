import { mount } from "@vue/test-utils";
import { beforeEach, describe, expect, it } from 'vitest';
import { createTestingPinia } from "@pinia/testing";

import i18n from "@/lang";
import VeeValidate from "@/validation";

import { useDarkModeStore } from "@/stores/dark-mode";

import LoadingIcon from "@/global-components/loading-icon/Main.vue";
import DarkModeSwitcher from "@/components/dark-mode-switcher/Main.vue";

import Login from "@/views/login/Main.vue";

describe('Login.vue', () => {
    it('should load properly', () => {
        const wrapper = mount(Login, {
            global: {
                components: {
                    DarkModeSwitcher,
                    LoadingIcon
                },
                plugins: [
                    i18n,
                    VeeValidate,
                    createTestingPinia()
                ]
            }
        });

        const mockDarkModeStore = useDarkModeStore();
        mockDarkModeStore.darkModeValue = false;

        expect(wrapper.text()).contains('Login');
    });
});