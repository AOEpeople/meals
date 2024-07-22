import MealsSummary from '@/components/participations/MealsSummary.vue';
import { describe, it } from '@jest/globals';
import { mount } from '@vue/test-utils';
import { IDay, IDish } from '@/api/getMealsNextThreeDays';
import { Diet } from '@/enums/Diet';

const iDishesEn: IDish[] = [
    {
        title: 'Test111',
        diet: Diet.MEAT
    },
    {
        title: 'Test112',
        diet: Diet.MEAT
    },
    {
        title: 'Combined Dish',
        diet: Diet.MEAT
    }
];

const iDishesDe: IDish[] = [
    {
        title: 'Test111',
        diet: Diet.MEAT
    },
    {
        title: 'Test112',
        diet: Diet.MEAT
    },
    {
        title: 'Kombi-Gericht',
        diet: Diet.MEAT
    }
];

const dayOne: IDay = {
    en: iDishesEn,
    de: iDishesDe,
    date: new Date('2023-05-10')
};

const dayTwo: IDay = {
    en: [iDishesEn[0], iDishesEn[1]],
    de: [iDishesDe[0], iDishesDe[1]],
    date: new Date('2023-05-11')
};

const dayThree: IDay = {
    en: [iDishesEn[0]],
    de: [iDishesDe[0]],
    date: new Date('2023-05-12')
};

const dayFour: IDay = {
    en: [],
    de: [],
    date: new Date('2023-05-15')
};

describe('Test MealsSummary', () => {
    it('should display three meals and no empty rows', () => {
        const wrapper = mount(MealsSummary, {
            props: {
                day: dayOne
            }
        });
        const testMeals = ['Test111', 'Test112', 'Combined Dish'];

        expect(wrapper.findAll('td')).toHaveLength(3);
        expect(wrapper.findAll('th')).toHaveLength(1);

        expect(wrapper.find('th').text()).toBe('Wednesday');
        for (const td of wrapper.findAll('td')) {
            expect(testMeals.includes(td.text())).toBe(true);
        }
    });

    it('should display two meals and an empty row', () => {
        const wrapper = mount(MealsSummary, {
            props: {
                day: dayTwo
            }
        });
        const testMeals = ['Test111', 'Test112', ''];

        expect(wrapper.findAll('td')).toHaveLength(3);
        expect(wrapper.findAll('th')).toHaveLength(1);

        expect(wrapper.find('th').text()).toBe('Thursday');
        for (const td of wrapper.findAll('td')) {
            expect(testMeals.includes(td.text())).toBe(true);
        }
    });

    it('should display one meal and two empty rows', () => {
        const wrapper = mount(MealsSummary, {
            props: {
                day: dayThree
            }
        });
        const testMeals = ['Test111', ''];

        expect(wrapper.findAll('td')).toHaveLength(3);
        expect(wrapper.findAll('th')).toHaveLength(1);

        expect(wrapper.find('th').text()).toBe('Friday');
        for (const td of wrapper.findAll('td')) {
            expect(testMeals.includes(td.text())).toBe(true);
        }
    });

    it('should display no meal and three empty rows', () => {
        const wrapper = mount(MealsSummary, {
            props: {
                day: dayFour
            }
        });

        expect(wrapper.findAll('td')).toHaveLength(3);
        expect(wrapper.findAll('th')).toHaveLength(1);

        expect(wrapper.find('th').text()).toBe('Monday');
        for (const td of wrapper.findAll('td')) {
            expect(td.text()).toBe('');
        }
    });
});
