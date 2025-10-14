import { mount } from '@vue/test-utils';
import useDetectClickOutside from '@/services/useDetectClickOutside';
import { ref, defineComponent } from 'vue';
import { describe, it, expect, vi } from 'vitest';

describe('useDetectClickOutside', () => {
  it('calls callback when click is outside', async () => {
    const spy = vi.fn();
    const outside = document.createElement('div');
    document.body.appendChild(outside);

    const wrapper = mount(defineComponent({
      setup() {
        const target = ref<HTMLElement | null>(null);
        useDetectClickOutside(target, spy);
        return { target };
      },
      template: '<div ref="target"><p>Inside</p></div>',
    }), { attachTo: document.body });

    // Klick außerhalb
    outside.click();
    expect(spy).toHaveBeenCalledTimes(1);

    // Klick innerhalb
    spy.mockClear();
    await wrapper.find('p').trigger('click');
    expect(spy).not.toHaveBeenCalled();
  });
});
