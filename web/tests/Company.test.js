import { mount } from "@vue/test-utils";
import { beforeEach, describe, expect, it, test } from 'vitest';
import { createTestingPinia } from "@pinia/testing";

import i18n from "@/lang";
import VeeValidate from "@/validation";

import { useUserContextStore } from "@/stores/user-context";
import { useSideMenuStore } from "@/stores/side-menu";

import LoadingIcon from "@/global-components/loading-icon/Main.vue";
import DataList from "@/global-components/data-list/Main.vue";
import AlertPlaceholder from "@/global-components/alert-placeholder/Main.vue";
import Modal from "@/global-components/modal";

import Company from "@/views/company/Company.vue";

describe.skip('Company.vue', () => {
    it('should load properly', () => {
        const wrapper = mount(Company, {
            global: {
                components: {
                    LoadingIcon
                },
                plugins: [
                    i18n,
                    VeeValidate,
                    createTestingPinia()
                ]
            }
        });

        const mockUserContextStore = useUserContextStore();
        mockUserContextStore.userContext = {};

        expect(wrapper.text()).contains('Company Lists');
    });
});