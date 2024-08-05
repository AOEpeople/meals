import { mount, shallowMount } from '@vue/test-utils';
import Footer from '@/components/Footer.vue';
import HelloTest from '@/components/test/HelloTest.vue';
import { computed } from 'vue';
import { describe, expect, test, vi } from 'vitest';

const MessageComponent = {
    template: '<p>{{ msg }}</p>',
    props: ['msg']
};

vi.mock('vue-i18n', () => ({
    useI18n: () => ({
        t: (key: string) => key,
        locale: computed(() => 'en')
    })
}));

describe('Unit tests are functional', () => {
    test('displays message', () => {
        const wrapper = mount(MessageComponent, {
            props: {
                msg: 'Hello world'
            }
        });

        // Assert the rendered text of the component
        expect(wrapper.text()).toContain('Hello world');
    });

    test('shallow mounts normal component', () => {
        const wrapper = shallowMount(HelloTest, {
            props: {
                name: 'Mustermann',
                header: 'Test'
            }
        });
        expect(wrapper.text()).toContain('Test');
    });

    test('mounts normal component with nested component', () => {
        const wrapper = mount(HelloTest, {
            props: {
                name: 'Mustermann',
                header: 'Test'
            }
        });
        expect(wrapper.find('p').text()).toBe('Hello Mustermann');
    });

    test('mounts component with i18n-dependency', () => {
        const wrapper = mount(Footer);
        expect(wrapper.text()).toMatch(/changeLanguage/);
    });
});
