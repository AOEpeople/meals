/* eslint-disable @typescript-eslint/ban-ts-comment */
import ParticipantsTableBody from '@/components/participations/ParticipantsTableBody.vue';
import ParticipantsTableSlot from '@/components/participations/ParticipantsTableSlot.vue';
import participations from '../fixtures/participations.json';
import { getShowParticipations } from '@/api/getShowParticipations';
import { ref } from 'vue';
import { describe, expect, it, test } from '@jest/globals';
import { flushPromises, mount, shallowMount } from '@vue/test-utils';
import useApi from '@/api/api';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(participations),
    request: asyncFunc,
    error: ref(false)
};

// @ts-expect-error ts doesn't like mocking with jest.fn()
useApi = jest.fn(useApi);
// @ts-expect-error continuation of expect error from line above
useApi.mockReturnValue(mockedReturnValue);

describe('Test functions of ParticipantsTableBody', () => {
    test('scrollAmount to return a positive number', () => {
        const wrapper = shallowMount(ParticipantsTableBody);

        // @ts-ignore ts does not find conditionally exported values for testing i.e. scrollAmount
        expect(wrapper.vm.scrollAmount(16)).toBeGreaterThan(0);
    });

    test('setScrollDirection sets scrollDirectionDown to true', () => {
        const wrapper = shallowMount(ParticipantsTableBody);

        const htmlDummyElement = { scrollTop: 0, clientHeight: 300, scrollHeight: 600 } as HTMLTableSectionElement;

        // @ts-ignore ts does not find conditionally exported values for testing i.e. setScrollDirection
        wrapper.vm.setScrollDirection(htmlDummyElement);

        // @ts-ignore ts does not find conditionally exported values for testing i.e. scrollDirectionDown
        expect(wrapper.vm.scrollDirectionDown).toBeTruthy();
    });

    test('setScrollDirection sets scrollDirectionDown to false', () => {
        const wrapper = shallowMount(ParticipantsTableBody);

        const htmlDummyElement = { scrollTop: 301, clientHeight: 300, scrollHeight: 600 } as HTMLTableSectionElement;

        // @ts-ignore ts does not find conditionally exported values for testing i.e. setScrollDirection
        wrapper.vm.setScrollDirection(htmlDummyElement);

        // @ts-ignore ts does not find conditionally exported values for testing i.e. scrollDirectionDown
        expect(wrapper.vm.scrollDirectionDown).toBeFalsy();
    });

    test('scrollDirectionDown is false and stays false when scrollTop is not 0', () => {
        const wrapper = shallowMount(ParticipantsTableBody);

        const htmlDummyElement = { scrollTop: 301, clientHeight: 300, scrollHeight: 600 } as HTMLTableSectionElement;

        // @ts-ignore ts does not find conditionally exported values for testing i.e. setScrollDirection
        wrapper.vm.setScrollDirection(htmlDummyElement);
        // @ts-ignore ts does not find conditionally exported values for testing i.e. scrollDirectionDown
        expect(wrapper.vm.scrollDirectionDown).toBeFalsy();
        // @ts-ignore ts does not find conditionally exported values for testing i.e. setScrollDirection
        wrapper.vm.setScrollDirection(htmlDummyElement);
        // @ts-ignore ts does not find conditionally exported values for testing i.e. scrollDirectionDown
        expect(wrapper.vm.scrollDirectionDown).toBeFalsy();
    });

    test('scrollDirectionDown is false and changes to true when scrollTop is 0', () => {
        const wrapper = shallowMount(ParticipantsTableBody);

        const htmlDummyElement = { scrollTop: 301, clientHeight: 300, scrollHeight: 600 } as HTMLTableSectionElement;

        // @ts-ignore ts does not find conditionally exported values for testing i.e. setScrollDirection
        wrapper.vm.setScrollDirection(htmlDummyElement);
        // @ts-ignore ts does not find conditionally exported values for testing i.e. scrollDrectionDown
        expect(wrapper.vm.scrollDirectionDown).toBeFalsy();
        htmlDummyElement.scrollTop = 0;
        // @ts-ignore ts does not find conditionally exported values for testing i.e. setScrollDirection
        wrapper.vm.setScrollDirection(htmlDummyElement);
        // @ts-ignore ts does not find conditionally exported values for testing i.e. scrollDirectionDown
        expect(wrapper.vm.scrollDirectionDown).toBeTruthy();
    });
});

describe('Test ParticipantsTableBody', () => {
    const { loadShowParticipations } = getShowParticipations();

    it('should render two slots with the correct slotNames', async () => {
        await loadShowParticipations();
        Element.prototype.scrollBy = () => void {};

        const wrapper = mount(ParticipantsTableBody);
        const slotNames = ['Active w/ limit', 'Active w/o limit'];

        await flushPromises();

        expect(wrapper.findAllComponents(ParticipantsTableSlot)).toHaveLength(2);

        const thElements = wrapper.findAll('th');
        expect(thElements).toHaveLength(2);
        for (const th of thElements) {
            expect(slotNames.includes(th.text())).toBe(true);
        }
    });
});
