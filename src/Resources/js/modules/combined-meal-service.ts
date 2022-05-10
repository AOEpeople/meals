import {MealService} from "./meal-service";

export class CombinedMealService {

    /**
     * No. of dish items in a combined dish.
     */
    private static readonly DISH_COUNT = 2;

    /**
     * Gets the available dishes for the combined dish.
     */
    public static getDishes($dayMealsContainer: JQuery): Dish[] {
        let dishes = CombinedMealService.getAllDishes($dayMealsContainer);
        let availableDishSlugs = CombinedMealService.getAvailableDishSlugs($dayMealsContainer);

        if (0 === availableDishSlugs.length) {
            return dishes;
        }

        let availableDishes: Dish[] = [];
        dishes.forEach((dish) => {
            if (dish.slug === undefined) {
                let dv = dish.variations.filter(dv => availableDishSlugs.includes(dv.slug));
                if (0 < dv.length) {
                    dish.variations = dv;
                    availableDishes.push(dish);
                }
            } else if (availableDishSlugs.includes(dish.slug)) {
                availableDishes.push(dish);
            }
        });

        return availableDishes;
    }

    /**
     * @param $checkbox       Combined Dish Checkbox
     * @param participantID   Participant's ID for the booked combined meal
     * @param bookedDishSlugs Dish IDs in booked combined meal
     */
    public static updateDishes($checkbox: JQuery, participantID: number, bookedDishSlugs: string[]) {
        let $dishContainer = $checkbox.closest('.meal-row');
        if (!CombinedMealService.isCombinedDish($dishContainer)) {
            return;
        }
        if (typeof participantID === 'undefined' || !Array.isArray(bookedDishSlugs) || 0 === bookedDishSlugs.length) {
            CombinedMealService.resetDish($dishContainer);
            return;
        }

        let $mealContainer = $dishContainer.closest('.meal');
        const dishes = CombinedMealService.getDishes($mealContainer);
        const success = CombinedMealService.updateBookedDishes($checkbox, dishes, bookedDishSlugs);

        if (success) {
            MealService.setParticipantId($checkbox, participantID);
            if (CombinedMealService.mealHasDishVariations($mealContainer)
                && !CombinedMealService.isLockedMeal($mealContainer)) {
                $dishContainer.find('.title').addClass('edit');
            }
        }
    }

    /**
     * @param $checkbox       Combined Dish Checkbox
     * @param $dishes         Available meal dishes on a given day
     * @param bookedDishSlugs Dish Slugs in booked combined meal
     */
    public static updateBookedDishes($checkbox: JQuery, $dishes: Dish[], bookedDishSlugs: string[]): boolean {
        let $dishContainer = $checkbox.closest('.meal-row');
        if (!CombinedMealService.isCombinedDish($dishContainer) ||
            !Array.isArray(bookedDishSlugs) ||
            0 === bookedDishSlugs.length) {
            return false;
        }

        let bdt = CombinedMealService.getBookedDishTitles(bookedDishSlugs, $dishes);

        if (CombinedMealService.DISH_COUNT === bdt.length) {
            // update dish description with titles of booked dishes
            const bookedDishTitles = bdt.map(dishTitle => $(`<div class="dish">${dishTitle}</div>`));
            $dishContainer.find('.description .dish-combination').empty().append(...bookedDishTitles);
            $dishContainer.find('.title').removeClass('no-description');
            // update booked dish IDs in data attribute
            $dishContainer.attr('data-booked-dishes', bookedDishSlugs.join(','));

            return true;
        }

        return false;
    }

    public static isCombinedDish($dishContainer: JQuery): boolean {
        return $dishContainer.hasClass('combined-meal');
    }

    private static getAllDishes($mealContainer: JQuery): Dish[] {
        let dishes: Dish[] = [];

        $mealContainer.find('.meal-row').each(function () {
            const $dishContainer = $(this);
            if (CombinedMealService.isCombinedDish($dishContainer)) {
                return;
            }

            let dish: Dish = {
                title: $dishContainer.find('.title').contents().get(0).nodeValue.trim(),
                slug: $dishContainer.data('slug'),
                variations: [],
                isCombined: false
            };
            $dishContainer.find('.variation-row').each(function () {
                const $dishVarRow = $(this);
                let dishVariation: DishVariation = {
                    title: $dishVarRow.find('.text-variation').text().trim(),
                    slug: $dishVarRow.data('slug'),
                };
                dish.variations.push(dishVariation);
            });
            dishes.push(dish);
        });

        return dishes;
    }

    private static getAvailableDishSlugs($dayMealsContainer: JQuery): string[] {
        let $combinedMeal = $dayMealsContainer.find('.combined-meal');
        if (0 === $combinedMeal.length) {
            console.log(`error: combined meal not found, date: ${$dayMealsContainer.data('date')}`);
            return;
        }

        let availableDishes = $combinedMeal.attr('data-available-dishes');

        return availableDishes === undefined || availableDishes === '' ? [] : availableDishes.split(',');
    }

    private static getBookedDishTitles(dishIDs: string[], dishes: Dish[] | DishVariation[]) {
        let dishTitles: string[] = [];
        dishes.forEach(function (dish) {
            let idx = dishIDs.indexOf(dish.slug);
            if (-1 < idx) {
                dishTitles.push(dish.title);
                dishIDs.slice(idx, 1);
            } else if (Array.isArray(dish.variations) && 0 < dish.variations.length) {
                let dvt = CombinedMealService.getBookedDishTitles(dishIDs, dish.variations);
                dishTitles.push(...dvt);
            }
        });

        return dishTitles;
    }

    private static resetDish($dishContainer: JQuery): void {
        const $checkbox = $dishContainer.find('input[type=checkbox]');
        MealService.setParticipantId($checkbox, null);

        let desc = $dishContainer.data('description');
        $dishContainer.find('.description .dish-combination').empty().text(desc);
        $dishContainer.find('.title').removeClass('edit');
        if (CombinedMealService.isCombinedDish($dishContainer)) {
            $dishContainer.find('.title').addClass('no-description');
        }
        $dishContainer.attr('data-id', '');
        $dishContainer.attr('data-booked-dishes', '');
    }

    private static isLockedMeal($mealContainer: JQuery): boolean {
        let lockDateTime = $mealContainer.data('lockDateTime');
        const mealLockDateTime = Date.parse(lockDateTime);

        return mealLockDateTime <= Date.now();
    }

    private static mealHasDishVariations($mealContainer: JQuery): boolean {
        return 0 < $mealContainer.find('.meal-row .variation-row').length;
    }
}

export interface Dish extends DishVariation {
    variations: DishVariation[];
    'isCombined': boolean;
}

export interface DishVariation {
    title: string;
    slug: string;
}
