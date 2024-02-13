import { IMealWithVariations } from '@/api/getShowParticipations';
import Meal from '@/components/participations/Meal.vue';
import { describe, expect } from '@jest/globals';
import { mount } from '@vue/test-utils';

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
    participations: 0,
    mealId: 5
};

describe('Test Meal', () => {
    it('should render a th-eleent with the name of the meal and no variations', () => {
        const wrapper = mount(Meal, {
            props: {
                meal: mealOne
            }
        });

        expect(wrapper.findAll('th')).toHaveLength(1);
        expect(wrapper.findAll('td')).toHaveLength(0);

        expect(wrapper.find('th').text()).toBe(mealOne.title.en);
    });

    it('should render a th-element with the name of the meal and two variations', () => {
        const wrapper = mount(Meal, {
            props: {
                meal: mealTwo
            }
        });

        expect(wrapper.findAll('th')).toHaveLength(1);
        expect(wrapper.findAll('td')).toHaveLength(2);

        expect(wrapper.find('th').text()).toBe(mealTwo.title.en);

        const variationNames = [mealTwo.variations[0].title.en, mealTwo.variations[1].title.en];
        const variationTds = wrapper.findAll('td');
        for (const variationTd of variationTds) {
            expect(variationNames.includes(variationTd.text()));
        }
    });

    it('should not display if the title of the meal is "Combined Dish"', () => {
        const wrapper = mount(Meal, {
            props: {
                meal: mealFive
            }
        });

        expect(wrapper.find('th').exists()).toBe(false);
        expect(wrapper.find('td').exists()).toBe(false);
        expect(wrapper.find('table').exists()).toBe(false);
    });
});
