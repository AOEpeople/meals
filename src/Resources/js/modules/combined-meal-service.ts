export class CombinedMealService {
    public static getCombinedMealDishes($mealContainer: JQuery): Dish[] {
        let dishes: Dish[] = [];
        $mealContainer.find('.meal-row').each(function () {
            const $mealRow = $(this);
            if (1 === $mealRow.data('combined')) {
                return;
            }

            let dish: Dish = {
                title: $mealRow.find('.title').contents().get(0).nodeValue.trim(),
                slug: $mealRow.data('slug'),
                variations: []
            };
            $mealRow.find('.variation-row').each(function () {
                const $dishVarRow = $(this);
                let dishVariation: DishVariation = {
                    title: $dishVarRow.find('.text-variation').text().trim(),
                    slug: $dishVarRow.data('slug')
                };
                dish.variations.push(dishVariation);
            });
            dishes.push(dish);
        });

        return dishes;
    }

    /**
     * @param $checkbox     Combined Dish Checkbox
     * @param participantID Participation ID for booked combined meal
     * @param bookedDishSlugs Dish IDs in booked combined meal
     */
    public static updateCombinedDish($checkbox: JQuery, participantID: number, bookedDishSlugs: string[]) {
        let $dishContainer = $checkbox.closest('.meal-row');

        if (Array.isArray(bookedDishSlugs) && (0 < bookedDishSlugs.length)) {
            let $mealContainer = $dishContainer.closest('.meal');
            const dishes = CombinedMealService.getCombinedMealDishes($mealContainer);
            let dt = CombinedMealService.getBookedDishTitles(bookedDishSlugs, dishes);
            if (0 < dt.length) {
                // update dish description with titles of booked dishes
                const bookedDishTitles = dt.map(dishTitle => $(`<div class="dish">${dishTitle}</div>`));
                $dishContainer.find('.description .dish-combination').empty().append(...bookedDishTitles);
                if (CombinedMealService.mealHasDishVariations($mealContainer)) {
                    $dishContainer.find('.title').addClass('edit');
                }

                // update booked dish IDs in data attribute
                $dishContainer.attr('data-id', participantID);
                $dishContainer.attr('data-booked-dishes', bookedDishSlugs.join(','));
            }

            return;
        }

        let desc = $dishContainer.data('description');
        $dishContainer.find('.description .dish-combination').empty().text(desc);
        $dishContainer.find('.title').removeClass('edit');
        $dishContainer.attr('data-id', '');
        $dishContainer.attr('data-booked-dishes', '');
    }

    /**
     * @param $checkbox       Combined Dish Checkbox
     * @param $dishes         Available meal dishes on a given day
     * @param bookedDishSlugs Dish Slugs in booked combined meal
     */
    public static updateBookedDishes($checkbox: JQuery, $dishes: Dish[], bookedDishSlugs: string[]) {
        let $dishContainer = $checkbox.closest('.meal-row');
        let bdt = CombinedMealService.getBookedDishTitles(bookedDishSlugs, $dishes);
        if (0 < bdt.length) {
            // update dish description with titles of booked dishes
            const bookedDishTitles = bdt.map(dishTitle => $(`<div class="dish">${dishTitle}</div>`));
            $dishContainer.find('.description .dish-combination').empty().append(...bookedDishTitles);
            // update booked dish IDs in data attribute
            $dishContainer.attr('data-booked-dishes', bookedDishSlugs.join(','));
        }
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

    private static mealHasDishVariations($mealContainer: JQuery): boolean {
        return 0 < $mealContainer.find('.meal-row .variation-row').length;
    }
}

export interface Dish extends DishVariation {
    variations: DishVariation[]
}

export interface DishVariation {
    title: string
    slug: string
}