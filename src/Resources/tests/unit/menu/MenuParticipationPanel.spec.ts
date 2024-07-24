import MenuParticipationPanel from '@/components/menu/MenuParticipationPanel.vue';
import { mount } from '@vue/test-utils';
import { MealDTO } from '@/interfaces/DayDTO';
import { Dictionary } from '@/types/types';

const testMeals: Dictionary<MealDTO[]> = {
    1: [
        {
            dishSlug: 'TestDish',
            mealId: 0,
            participationLimit: 0
        },
        {
            dishSlug: 'TestDish2',
            mealId: 1,
            participationLimit: 17
        }
    ],
    2: [
        {
            dishSlug: 'TestDish3',
            mealId: 2,
            participationLimit: 23
        }
    ]
};

describe('Test MenuParticipationPanel', () => {
    it('should contain the correct text', () => {
        const wrapper = mount(MenuParticipationPanel, {
            props: {
                meals: testMeals,
                close: () => void 0
            }
        });

        expect(wrapper.find('span').text()).toMatch(/Limit/);
    });

    it('should contain the correct number of MealParticipationInputs', () => {
        const wrapper = mount(MenuParticipationPanel, {
            props: {
                meals: testMeals,
                close: () => void 0
            }
        });

        expect(wrapper.findAllComponents({ name: 'MealParticipationInput' })).toHaveLength(3);
    });

    it('should contain the correct data in the MealParticipationInputs', () => {
        const wrapper = mount(MenuParticipationPanel, {
            props: {
                meals: testMeals,
                close: () => void 0
            }
        });

        const mealParticipationInputs = wrapper.findAllComponents({ name: 'MealParticipationInput' });
        expect(mealParticipationInputs[0].props('meal')).toEqual(testMeals[1][0]);
        expect(mealParticipationInputs[1].props('meal')).toEqual(testMeals[1][1]);
        expect(mealParticipationInputs[2].props('meal')).toEqual(testMeals[2][0]);
    });
});
