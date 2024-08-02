import ParticipantsTableHead from '@/components/participations/ParticipantsTableHead.vue';
import { nextTick, reactive } from 'vue';
import { shallowMount } from '@vue/test-utils';
import { vi, describe, beforeEach, it, expect } from 'vitest';

let mockedGetShowParticipations = vi.fn(() => []);
const loadMock = reactive({ loaded: true });
vi.mock('@/api/getShowParticipations', () => ({
    getShowParticipations: () => ({
        loadedState: loadMock,
        getMealsWithVariations: mockedGetShowParticipations
    })
}));

describe('Test function call of ParticipantsTableHead', () => {
    beforeEach(() => {
        mockedGetShowParticipations = vi.fn(() => []);
        loadMock.loaded = true;
    });

    it('should call getShowParticipations if loadedState is true', () => {
        shallowMount(ParticipantsTableHead);

        expect(mockedGetShowParticipations).toHaveBeenCalledTimes(1);
    });

    it('should call getShowParticipations once loadedState switches to true', async () => {
        loadMock.loaded = false;
        shallowMount(ParticipantsTableHead);

        expect(mockedGetShowParticipations).not.toHaveBeenCalled();

        loadMock.loaded = true;
        await nextTick();

        expect(mockedGetShowParticipations).toHaveBeenCalledTimes(1);
    });
});
