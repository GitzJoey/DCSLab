import { mount } from "@vue/test-utils";
import { describe, expect, it } from 'vitest';

import Login from "@/views/login/Main.vue";

describe('Login.vue', () => {
    it('should load properly', () => {
        const wrapper = mount(Login);

        expect(wrapper.text()).contains('Login');
    });
});