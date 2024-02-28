import ParticipantsListByDayVue from "@/components/participations/ParticipantsListByDay.vue";
import { describe, it, expect } from "@jest/globals";
import { mount } from "@vue/test-utils";
import { computed } from "vue";

const mockSetFilter = jest.fn();


jest.mock('@/services/filterParticipantsList', () => ({
  filterParticipantsList: () => ({
    setFilter: mockSetFilter,
    filteredParticipants: computed<string[]>(()=> [])
})
}));

describe('Test ParticipantsSearchBar', () => {
    it('should render with the correct i18n text', () => {
        const wrapper = mount(ParticipantsListByDayVue, {
          props: {
            date: '28-02-2024',
            weekday: 'Wednesday',
            dateString: '3/5',
          }
        });

        expect(wrapper.find('input').attributes('placeholder')).toMatch(/menu.search/);
    });

    it('should call setFilter when typing in the input', async () => {
        const wrapper = mount(ParticipantsListByDayVue, {
          props: {
            date: '28-02-2024',
            weekday: 'Wednesday',
            dateString: '3/5',
          }
        });

        await wrapper.find('input').setValue('test');
        expect(mockSetFilter).toHaveBeenCalledTimes(1);

        await wrapper.find('input').setValue('test2');
        expect(mockSetFilter).toHaveBeenCalledTimes(2);
    });
});