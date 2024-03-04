import ParticipantsListByDayVue from "@/components/participations/ParticipantsListByDay.vue";
import { describe, it, expect } from "@jest/globals";
import { mount } from "@vue/test-utils";
import FilterInputVue from "@/components/misc/FilterInput.vue";
import { computed } from "vue";

const mockSetFilter = jest.fn();
const filteredParticipant = jest.fn();

jest.mock('@/services/filterParticipantsList', () => ({
  filterParticipantsList: () => ({
    setFilter: mockSetFilter,
    filteredParticipants: computed<string[]>(()=> [])
})
}));

describe('Test ParticipantByDayModal', () => {
  it('should render with the correct i18n text', () => {
    const wrapper = mount(ParticipantsListByDayVue, {
      props: {
        date: '28-02-2024',
        weekday: 'Wednesday',
        dateString: '3/5',
      }
    });

        expect(wrapper.find('title').text()).toMatch(/printList.title/);
        expect(wrapper.findComponent(FilterInputVue).exists()).toBe(true);
    });
  it('should render the table with the correct amount of rows', () => {
    const wrapper = mount(ParticipantsListByDayVue, {
      props: {
        date: '28-02-2024',
        weekday: 'Wednesday',
        dateString: '3/5',
      }
    });

        const tds = wrapper.findAll('td');
        expect(tds.length).toBe(filteredParticipant.length);
    });
    it('should render the table with the correct information', () => {
      const wrapper = mount(ParticipantsListByDayVue, {
        props: {
          date: '28-02-2024',
          weekday: 'Wednesday',
          dateString: '3/5',
        }
      });

          const tds = wrapper.findAll('td');
          tds.forEach((td, index) => {
            expect(td.text()).toBe(filteredParticipant[index]);
          });
      });
  });
