import { IBookedData, IMealWithVariations } from "@/api/getShowParticipations";
import ParticipantsTableSlot from "@/components/participations/ParticipantsTableSlot.vue";
import ParticipantsTableRow from "@/components/participations/ParticipantsTableRow.vue";
import { describe, it } from "@jest/globals";
import { mount } from "@vue/test-utils";
import { Dictionary } from "types/types";

const bookedDataOne: IBookedData = { booked: [1] };
const bookedDataTwo: IBookedData = { booked: [1, 3] };
const bookedDataThree: IBookedData = { booked: [4] };
const bookedDataFour: IBookedData = { booked: [1, 4] };

const mealOne: IMealWithVariations = {
    title: {
        en: 'Test1',
        de: 'Test1'
    },
    variations: [],
    participations: 3,
    mealId: 1
}
const mealThree: IMealWithVariations = {
    title: {
        en: 'Test3',
        de: 'Test3'
    },
    variations: [],
    participations: 1,
    mealId: 3
}
const mealFour: IMealWithVariations = {
    title: {
        en: 'Test4',
        de: 'Test4'
    },
    variations: [],
    participations: 2,
    mealId: 4
}
const mealTwo: IMealWithVariations = {
    title: {
        en: 'Test2',
        de: 'Test2'
    },
    variations: [mealThree, mealFour],
    participations: 5,
    mealId: 2
}

const participantsData: Dictionary<IBookedData> = {
    'testNameOne': bookedDataOne,
    'testNameTwo': bookedDataTwo,
    'testNameThree': bookedDataThree,
    'testNameFour': bookedDataFour
}

describe('Test ParticipationsTableSlot', () => {
    it('should display the slotname and render four ParticipantTableRows', () => {
        const meals = [mealOne, mealTwo];
        const slotname = 'testslot 12:00';

        const wrapper = mount(ParticipantsTableSlot, {
            props: {
                slotName: slotname,
                participants: participantsData,
                meals: meals
            }
        });

        expect(wrapper.find('th').text()).toEqual(slotname);
        expect(wrapper.findAllComponents(ParticipantsTableRow)).toHaveLength(4);
    });
});