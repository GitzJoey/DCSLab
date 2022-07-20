import { mount } from "@vue/test-utils";
import { beforeEach, describe, expect, it, test } from 'vitest';
import { createTestingPinia } from "@pinia/testing";

import i18n from "@/lang";
import VeeValidate from "@/validation";

import _ from "lodash";

import { useUserContextStore } from "@/stores/user-context";
import { useSideMenuStore } from "@/stores/side-menu";

import LoadingIcon from "@/global-components/loading-icon/Main.vue";
import DataList from "@/global-components/data-list/Main.vue";
import AlertPlaceholder from "@/global-components/alert-placeholder/Main.vue";
import { Modal, ModalHeader, ModalBody, ModalFooter } from "@/global-components/modal";
import { Alert } from "@/global-components/alert";
import Tippy from "@/global-components/tippy/Main.vue";

import Company from "@/views/company/Company.vue";

describe.skip('Company.vue', () => {
    it('should load properly', () => {
        const wrapper = mount(Company, {
            global: {
                components: {
                    LoadingIcon,
                    AlertPlaceholder,
                    DataList,
                    Modal,
                    ModalBody,
                    Alert,
                    Tippy
                },
                plugins: [
                    i18n,
                    VeeValidate,
                    createTestingPinia()
                ],
                provide: {
                    $_() {
                        return _;
                    }
                }
            }
        });

        const mockUserContextStore = useUserContextStore();
        mockUserContextStore.userContext = {};

        expect(wrapper.text()).contains('Company Lists');
    });
});