import { onMounted, reactive, getCurrentInstance } from 'vue';
import { useWeeks } from '@/stores/weeksStore';
import { useDishes } from '@/stores/dishesStore';

/**
 * Constructs a map from meal id to dish id for a given week and returns the map.
 * @param weekId The id of the week to construct the map for.
 */
export function useMealIdToDishId(weekId: number) {
    const { getDishBySlug, fetchDishes, DishesState } = useDishes();
    const { fetchWeeks, getWeekById, WeeksState } = useWeeks();

    const mealIdToDishIdDict = reactive<Map<number, number>>(new Map());

    async function init() {
        if (WeeksState.weeks.length === 0) await fetchWeeks();
        if (DishesState.dishes.length === 0) await fetchDishes();

        const week = getWeekById(weekId);
        if (!week) return;

        for (const day of Object.values(week.days ?? {})) {
            for (const meals of Object.values(day.meals)) {
                meals.forEach((meal) => {
                    const dish = getDishBySlug(meal.dish);
                    const dishId = dish ? dish.id : -1;
                    mealIdToDishIdDict.set(meal.id, dishId);
                });
            }
        }
    }

    // Check if we're in a component context
    const instance = getCurrentInstance();
    if (instance) {
        onMounted(() => {
            init();
        });
    } else {
        // Fallback for tests or non-component usage
        init();
    }

    return { mealIdToDishIdDict, init };
}
