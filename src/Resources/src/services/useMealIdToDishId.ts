import { onMounted, reactive } from "vue";
import { useWeeks } from "@/stores/weeksStore";
import { useDishes } from "@/stores/dishesStore";


export function useMealIdToDishId(weekId: number) {

    const { getDishBySlug, fetchDishes, DishesState } = useDishes();
    const { fetchWeeks, getWeekById, WeeksState } = useWeeks();

    const mealIdToDishIdDict = reactive<Map<number, number>>(new Map());

    onMounted(async () => {
        if (WeeksState.weeks.length === 0) {
            await fetchWeeks();
        }
        if (DishesState.dishes.length === 0) {
            await fetchDishes();
        }

        const week = getWeekById(weekId);

        for(const day of Object.values(week.days)) {
            for(const meals of Object.values(day.meals)) {
                meals.forEach(meal => {
                    const dishId = getDishBySlug(meal.dish).id
                    mealIdToDishIdDict.set(meal.id, dishId);
                });
            }
        }
    });

    return { mealIdToDishIdDict };
}