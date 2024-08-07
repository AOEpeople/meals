import { IMealWithVariations } from '@/api/getShowParticipations';
import MealHead from '@/components/participations/MealHead.vue';
import { Diet } from '@/enums/Diet';
import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';

const mealOne: IMealWithVariations = {
    title: {
        en: 'Test1',
        de: 'Test1'
    },
    variations: [],
    participations: 3,
    mealId: 1,
    diet: Diet.MEAT
};
const mealThree: IMealWithVariations = {
    title: {
        en: 'Test3',
        de: 'Test3'
    },
    variations: [],
    participations: 1,
    mealId: 3,
    diet: Diet.MEAT
};
const mealFour: IMealWithVariations = {
    title: {
        en: 'Test4',
        de: 'Test4'
    },
    variations: [],
    participations: 2,
    mealId: 4,
    diet: Diet.MEAT
};
const mealTwo: IMealWithVariations = {
    title: {
        en: 'Test2',
        de: 'Test2'
    },
    variations: [mealThree, mealFour],
    participations: 5,
    mealId: 2,
    diet: Diet.MEAT
};

describe('Test MealHead', () => {
    it('should render the meal title and no variations', () => {
        const wrapper = mount(MealHead, {
            props: {
                meal: mealOne
            }
        });

        expect(wrapper.find('.meal-header-test').text()).toEqual(mealOne.title.en);
        expect(wrapper.findAll('.meal-variations-test')).toHaveLength(0);
    });

    it('should render the meal title and two variations', () => {
        const variationTitles = [mealThree.title.en, mealFour.title.en];
        const wrapper = mount(MealHead, {
            props: {
                meal: mealTwo
            }
        });

        const variations = wrapper.findAll('.meal-variations-test');

        expect(wrapper.find('.meal-header-test').text()).toEqual(mealTwo.title.en);
        expect(variations).toHaveLength(2);

        for (const variation of variations) {
            expect(variationTitles.includes(variation.text())).toBe(true);
        }
    });
});
