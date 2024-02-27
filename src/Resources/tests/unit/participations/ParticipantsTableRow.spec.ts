import { IBookedData, IMealWithVariations } from '@/api/getShowParticipations';
import ParticipantsTableRow from '@/components/participations/ParticipantsTableRow.vue';
import { describe, expect, it } from '@jest/globals';
import { mount } from '@vue/test-utils';

const bookedDataOne: IBookedData = { booked: [1], isOffering: [false] };
const bookedDataTwo: IBookedData = { booked: [1, 3, 5], isOffering: [false] };

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

const mealFive: IMealWithVariations = {
    title: {
        en: 'Combined Dish',
        de: 'Kombi-Gericht'
    },
    variations: [],
    participations: 1,
    mealId: 5
};

describe('Test ParticipantsTableRow', () => {
    it('should contain three td-elements', () => {
        const wrapper = mount(ParticipantsTableRow, {
            props: {
                participantName: 'test-user',
                bookedMeals: bookedDataOne,
                meals: [mealOne, mealTwo]
            }
        });

        expect(wrapper.findAll('td')).toHaveLength(3);
    });

    it('should contain the participants name', () => {
        const wrapper = mount(ParticipantsTableRow, {
            props: {
                participantName: 'test-user',
                bookedMeals: bookedDataOne,
                meals: [mealOne, mealTwo]
            }
        });

        expect(wrapper.text()).toMatch(/test-user/);
    });

    it('should contain four td-elements with two variations, one normal meal and a combi meal', () => {
        const wrapper = mount(ParticipantsTableRow, {
            props: {
                participantName: 'test-user',
                bookedMeals: bookedDataTwo,
                meals: [mealOne, mealTwo, mealFive]
            }
        });

        expect(wrapper.findAll('td')).toHaveLength(4);

        expect(wrapper.find('.variations-class').exists()).toBe(true);
        expect(wrapper.findAll('.variations-class')).toHaveLength(2);

        expect(wrapper.find('.no-variations-class').exists()).toBe(true);
        expect(wrapper.findAll('.no-variations-class')).toHaveLength(2);

        expect(wrapper.findAll('.check-circle-icon')).toHaveLength(1);
        expect(wrapper.findAll('.combined-meal')).toHaveLength(2);
    });
});
