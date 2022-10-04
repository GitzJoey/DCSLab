import { mount } from "@vue/test-utils";
import { beforeEach, describe, expect, it, test } from 'vitest';

import { Alert } from "@/global-components/alert";
import AlertPlaceholder from "@/global-components/alert-placeholder/Main.vue";

describe('AlertPlaceholder Tests', () => 
{
    const wrapper = mount(AlertPlaceholder, {
        global: {
            components: {
                Alert
            },
            stubs: {
                AlertCircleIcon: {
                    template: '<span />'
                }
            }
        }
    });

    it('should load properly', async () => {
        await wrapper.setProps({
            alertType: 'danger',
            messages: {
                'test': 'testing'
            },
            title: 'test1'
        });

        expect(wrapper.text()).contains('test1');
    });
});