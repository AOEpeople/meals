import MealParticipationInput from '@/components/menu/MealParticipationInput.vue';
import { Dish } from '@/stores/dishesStore';
import { MealDTO } from '@/interfaces/DayDTO';
import { Ref, ref } from 'vue';
import { mount } from '@vue/test-utils';

const testMeal: Ref<MealDTO> = ref({
    dishSlug: 'TestDish',
    mealId: 0,
    participationLimit: 0
});

const mockGetDishBySlug = jest.fn((slug: string) => {
    const mockDish: Dish = {
        id: 0,
        slug: slug,
        titleDe: `${slug}De`,
        titleEn: `${slug}En`,
        categoryId: 0,
        oneServingSize: false,
        parentId: 0,
        variations: []
    };
    return mockDish;
});

jest.mock('@/stores/dishesStore', () => ({
    useDishes: () => ({
        getDishBySlug: mockGetDishBySlug
    })
}));

describe('Test MealParticipationInput', () => {
    it('should contain the correct text', () => {
        const wrapper = mount(MealParticipationInput, {
            props: {
                meal: testMeal.value
            }
        });

        expect(wrapper.find('span').text()).toEqual(`${testMeal.value.dishSlug}En`);
    });

    it('should change the participationLimit on input', async () => {
        const wrapper = mount(MealParticipationInput, {
            props: {
                meal: testMeal.value
            }
        });

        const input = wrapper.find('input');
        await input.setValue(5);

        expect(testMeal.value.participationLimit).toEqual(5);
    });
});
