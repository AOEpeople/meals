import { IBookedData, IMealWithVariations } from '@/api/getShowParticipations';
import ParticipantsTableData from '@/components/participations/ParticipantsTableData.vue';
import { describe, it } from '@jest/globals';
import { mount } from '@vue/test-utils';

const bookedDataOne: IBookedData = { booked: [1], isOffering: [false] };
const bookedDataTwo: IBookedData = { booked: [1, 3], isOffering: [false] };
const bookedDataThree: IBookedData = { booked: [], isOffering: [false] };

const mealOne: IMealWithVariations = {
    title: {
        en: 'Test1',
        de: 'Test1'
    },
    variations: [],
    participations: 3,
    mealId: 1
};
const mealThree: IMealWithVariations = {
    title: {
        en: 'Test3',
        de: 'Test3'
    },
    variations: [],
    participations: 1,
    mealId: 3
};
const mealFour: IMealWithVariations = {
    title: {
        en: 'Test4',
        de: 'Test4'
    },
    variations: [],
    participations: 2,
    mealId: 4
};
const mealTwo: IMealWithVariations = {
    title: {
        en: 'Test2',
        de: 'Test2'
    },
    variations: [mealThree, mealFour],
    participations: 5,
    mealId: 2
};

describe('Test ParticipantsTableData', () => {
    it('should render a CheckCircleIcon and no variations', () => {
        const wrapper = mount(ParticipantsTableData, {
            props: {
                bookedMeals: bookedDataOne,
                meal: mealOne,
                bookedCombinedMeal: false
            }
        });

        expect(wrapper.find('.no-variations-class').exists()).toBe(true);
        expect(wrapper.find('.check-circle-icon').exists()).toBe(true);
        expect(wrapper.find('.combined-meal').exists()).toBe(false);
        expect(wrapper.find('.variations-class').exists()).toBe(false);
    });

    it('should render a combined-meal icon and no variations', () => {
        const wrapper = mount(ParticipantsTableData, {
            props: {
                bookedMeals: bookedDataOne,
                meal: mealOne,
                bookedCombinedMeal: true
            }
        });

        expect(wrapper.find('.no-variations-class').exists()).toBe(true);
        expect(wrapper.find('.check-circle-icon').exists()).toBe(false);
        expect(wrapper.find('.combined-meal').exists()).toBe(true);
        expect(wrapper.find('.variations-class').exists()).toBe(false);
    });

    it('should render a CheckIconCircle and two variations', () => {
        const wrapper = mount(ParticipantsTableData, {
            props: {
                bookedMeals: bookedDataTwo,
                meal: mealTwo,
                bookedCombinedMeal: false
            }
        });

        expect(wrapper.find('.no-variations-class').exists()).toBe(false);
        expect(wrapper.find('.check-circle-icon').exists()).toBe(true);
        expect(wrapper.find('.combined-meal').exists()).toBe(false);
        expect(wrapper.find('.variations-class').exists()).toBe(true);
        expect(wrapper.findAll('.variations-class')).toHaveLength(2);
    });

    it('should render a CheckIconCircle and two variations', () => {
        const wrapper = mount(ParticipantsTableData, {
            props: {
                bookedMeals: bookedDataTwo,
                meal: mealTwo,
                bookedCombinedMeal: true
            }
        });

        expect(wrapper.find('.no-variations-class').exists()).toBe(false);
        expect(wrapper.find('.check-circle-icon').exists()).toBe(false);
        expect(wrapper.find('.combined-meal').exists()).toBe(true);
        expect(wrapper.find('.variations-class').exists()).toBe(true);
        expect(wrapper.findAll('.variations-class')).toHaveLength(2);
    });

    it('should render two empty variations', () => {
        const wrapper = mount(ParticipantsTableData, {
            props: {
                bookedMeals: bookedDataThree,
                meal: mealTwo,
                bookedCombinedMeal: false
            }
        });

        expect(wrapper.find('.no-variations-class').exists()).toBe(false);
        expect(wrapper.find('.check-circle-icon').exists()).toBe(false);
        expect(wrapper.find('.combined-meal').exists()).toBe(false);
        expect(wrapper.find('.variations-class').exists()).toBe(true);
        expect(wrapper.findAll('.variations-class')).toHaveLength(2);
    });

    it('should render no variations and an empty cell', () => {
        const wrapper = mount(ParticipantsTableData, {
            props: {
                bookedMeals: bookedDataThree,
                meal: mealOne,
                bookedCombinedMeal: false
            }
        });

        expect(wrapper.find('.no-variations-class').exists()).toBe(true);
        expect(wrapper.find('.check-circle-icon').exists()).toBe(false);
        expect(wrapper.find('.combined-meal').exists()).toBe(false);
        expect(wrapper.find('.variations-class').exists()).toBe(false);
        expect(wrapper.findAll('.no-variations-class')).toHaveLength(1);
    });
});
