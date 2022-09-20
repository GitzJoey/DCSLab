import { mount } from "@vue/test-utils";
import { beforeEach, describe, expect, it, test } from 'vitest';

import _ from "lodash";

import { Alert } from "@/global-components/alert";
import AlertPlaceholder from "@/global-components/alert-placeholder/Main.vue";

describe.skip('AlertPlaceholder Tests', () => {
    it('should load properly', () => {
        const wrapper = mount(AlertPlaceholder, {
            global: {
                components: {
                    Alert
                },
                stubs: {
                    AlertCircleIcon: {
                        template: '<span />'
                    }
                },
                provide: {
                    $_() {
                        return _;
                    }
                }
            }
        });

        wrapper.setProps({
            alertType: 'danger',
            messages: {},
            title: 'test'
        });

        expect(wrapper.text()).contains('test');
    });
});