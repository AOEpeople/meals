import ParticipantsTableHead from '@/components/participations/ParticipantsTableHead.vue';
import { nextTick, reactive } from 'vue';
import { describe, expect, it } from '@jest/globals';
import { shallowMount } from '@vue/test-utils';

let mockedGetShowParticipations = jest.fn(() => []);
const loadMock = reactive({ loaded: true });
jest.mock('@/api/getShowParticipations', () => ({
    getShowParticipations: () => ({
        loadedState: loadMock,
        getMealsWithVariations: mockedGetShowParticipations
    })
}));

describe('Test function call of ParticipantsTableHead', () => {
    beforeEach(() => {
        mockedGetShowParticipations = jest.fn(() => []);
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
