import ParticipantsListByDayVue from "@/components/participations/ParticipantsListByDay.vue";
import { filterParticipantsList } from "@/services/filterParticipantsList";
import { describe, it, expect } from "@jest/globals";
import { mount } from "@vue/test-utils";

const mockSetFilter = jest.fn();

jest.mock('@/stores/dishesStore', () => ({
  filterParticipantsList: () => ({
    setFilter: mockSetFilter
})
}));

describe('Test ParticipantsSearchBar', () => {
    it('should render with the correct i18n text', () => {
        const wrapper = mount(ParticipantsListByDayVue);

        expect(wrapper.find('input').attributes('placeholder')).toMatch(/menu.search/);
    });

    it('should call setFilter when typing in the input', async () => {
        const wrapper = mount(ParticipantsListByDayVue);

        await wrapper.find('input').setValue('test');
        expect(mockSetFilter).toHaveBeenCalledTimes(1);

        await wrapper.find('input').setValue('test2');
        expect(mockSetFilter).toHaveBeenCalledTimes(2);
    });
});