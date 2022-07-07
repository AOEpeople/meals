import {CombinedMealDialog, SerializedFormData} from "../modules/combined-meal-dialog";
import {Dish, DishVariation} from "../modules/combined-meal-service";
import AjaxErrorHandler from "../modules/ajax-error-handler";

interface DeleteResponseData {
    participantsCount: number;
    url: string;
}

interface JoinResponseData {
    id: number,
    participantsCount: number;
    url: string;
    bookedDishSlugs: string[];
}

// function type for request success/failure handlers
type ReqFailureFn = (error: string) => void;
type ReqSuccessFn = (data: unknown) => void;

enum MealToggleAction {
    Join = 'join',
    Cancel = 'delete'
}

export default class AdminParticipationEditView {
    constructor() {
        this.initEvents();
    }

    private initEvents(): void {
        $('.table-content')
            // edit meal participation
            .on(
                'click',
                '.table-row .table-data.text',
                this.handleEditMealParticipation
            )
            // toggle simple meal participation
            .on(
                'click',
                '.table-row.editing .meal-participation[data-combined=0]',
                this.handleToggleSimpleMealParticipation.bind(this)
            )
            // toggle combined meal participation
            .on(
                'click',
                '.table-row.editing .meal-participation[data-combined=1]',
                this.handleToggleCombinedMealParticipation.bind(this)
            );
    }

    private handleEditMealParticipation(event: JQuery.TriggeredEvent): void {
        let $participantRow = $(event.target).closest('.table-row');
        const $isParticipantRowEditable = $participantRow.hasClass('editing');

        // reset previous editable user row (if any)
        $participantRow.closest('.table').find('.table-row.editing').each(function(idx, participantRow){
            $(participantRow)
                .removeClass('editing')
                .find('.table-data').each(function(idx, mealContainer){
                    let $mealContainer = $(mealContainer);
                    let iconClass = 'glyphicon';
                    if ($mealContainer.hasClass('participating')) {
                        iconClass += ' glyphicon-ok';
                    }
                    $mealContainer.find('i:first').attr('class', iconClass);
                });
        });

        // selected user row was in edit mode, and now has been reset; do nothing
        if ($isParticipantRowEditable) {
            return;
        }

        // make current participant row editable
        $participantRow.addClass('editing')
        $participantRow.find('.table-data').each(function(idx, mealContainer){
            let $mealContainer = $(mealContainer);
            const participationStatusIconClass = $mealContainer.hasClass('participating') ? 'glyphicon-check' : 'glyphicon-unchecked';
            $mealContainer.find('i:first').addClass(participationStatusIconClass);
        });
    }

    private handleToggleSimpleMealParticipation(event: JQuery.TriggeredEvent): void {
        let $mealContainer = $(event.target).closest('[data-combined]');
        const action = $mealContainer.attr('data-action') as MealToggleAction;
        if (action === MealToggleAction.Join) {
            this.joinSimpleMeal($mealContainer);
        } else {
            this.cancelSimpleMeal($mealContainer);
        }
    }

    private joinSimpleMeal($mealContainer: JQuery): void {
        const url = $mealContainer.attr('data-action-url');
        this.sendRequest(
            url,
            null,
            (data: JoinResponseData) => AdminParticipationEditView.simpleMealJoinSuccess($mealContainer, data),
            (error: string) => AdminParticipationEditView.toggleFailure(error, MealToggleAction.Join, url)
        );
    }

    private static simpleMealJoinSuccess($mealContainer: JQuery, data: JoinResponseData): void {
        $mealContainer
            .attr('data-action-url', data.url)
            .attr('data-action', MealToggleAction.Cancel)
            .toggleClass('participating')
            .find('i:first')
            .removeClass('glyphicon-unchecked')
            .addClass('glyphicon-check');

        const dishSlug = $mealContainer.data('dishSlug');
        const day = $mealContainer.data('date');
        AdminParticipationEditView.updateParticipantCount(dishSlug, day, data.participantsCount);
    }

    private cancelSimpleMeal($mealContainer: JQuery): void {
        const url = $mealContainer.attr('data-action-url');
        this.sendRequest(
            url,
            null,
            (data: DeleteResponseData) => AdminParticipationEditView.simpleMealCancelSuccess($mealContainer, data),
            (error: string) => AdminParticipationEditView.toggleFailure(error, MealToggleAction.Cancel, url)
        );
    }

    private static simpleMealCancelSuccess($mealContainer: JQuery, data: DeleteResponseData): void {
        $mealContainer
            .attr('data-action-url', data.url)
            .attr('data-action', MealToggleAction.Join)
            .toggleClass('participating')
            .find('i:first')
            .removeClass('glyphicon-check')
            .addClass('glyphicon-unchecked');

        const dishSlug = $mealContainer.data('dishSlug');
        const day = $mealContainer.data('date');
        AdminParticipationEditView.updateParticipantCount(dishSlug, day, data.participantsCount);
    }

    private handleToggleCombinedMealParticipation(event: JQuery.TriggeredEvent): void {
        let $mealContainer = $(event.target).closest('[data-combined]');
        const action = $mealContainer.attr('data-action');

        if (MealToggleAction.Cancel === action) {
            this.cancelCombinedMeal($mealContainer);
            return;
        }

        this.joinCombinedMeal($mealContainer);
    }

    private joinCombinedMeal($mealContainer: JQuery): void {
        const day = $mealContainer.data('date');
        let dishes = AdminParticipationEditView.getDishesOn(day);
        const dishSlug = $mealContainer.data('dishSlug');
        const dish = AdminParticipationEditView.findDish(dishSlug, dishes);

        if (null === dish) {
            console.log(`dish not found, slug: ${dishSlug}`);
            return;
        }

        if (AdminParticipationEditView.dishesContainVariation(dishes)) {
            this.joinCombinedMealWithVariations($mealContainer);
        } else {
            this.joinCombinedMealWithoutVariations($mealContainer);
        }
    }

    private joinCombinedMealWithVariations($mealContainer: JQuery): void {
        let self = this;
        const day = $mealContainer.data('date');
        const dishes = AdminParticipationEditView.getDishesOn(day);
        const dishSlug = $mealContainer.data('dishSlug');
        const dish = AdminParticipationEditView.findDish(dishSlug, dishes);
        let cmd = new CombinedMealDialog(
            dish.title,
            dishes,
            [],
            null,
            {
                ok: function (payload: SerializedFormData[]) {
                    const url = $mealContainer.attr('data-action-url');
                    self.sendRequest(
                        url,
                        payload,
                        (data: JoinResponseData) => AdminParticipationEditView.combinedMealJoinSuccess($mealContainer, data),
                        (error: string) => AdminParticipationEditView.toggleFailure(error, MealToggleAction.Join, url)
                    );
                }
            }
        );
        cmd.open();
    }

    private joinCombinedMealWithoutVariations($mealContainer: JQuery): void {
        const day = $mealContainer.data('date');
        const dishes = AdminParticipationEditView.getDishesOn(day);
        const dishSlugs = AdminParticipationEditView.getSimpleDishSlugs(dishes);

        let payload: SerializedFormData[] = [];
        dishSlugs.forEach(function (slug, i) {
            payload.push({
                'name': `dishes[${i}]`,
                'value': slug
            });
        });

        const url = $mealContainer.attr('data-action-url');
        this.sendRequest(
            url,
            payload,
            (data: JoinResponseData) => AdminParticipationEditView.combinedMealJoinSuccess($mealContainer, data),
            (error: string) => AdminParticipationEditView.toggleFailure(error, MealToggleAction.Join, url)
        );
    }

    private static combinedMealJoinSuccess($mealContainer: JQuery, data: JoinResponseData): void {
        $mealContainer
            .addClass('participating')
            .attr('data-action', MealToggleAction.Cancel)
            .attr('data-action-url', data.url)
            .find('.glyphicon')
            .removeClass('glyphicon-unchecked')
            .addClass('glyphicon-check glyphicon-ok');

        const day = $mealContainer.data('date');
        $(`.table-row.editing .meal-participation[data-date='${day}'][data-combined='0']`).each(function(){
            if (data.bookedDishSlugs.includes($(this).data('dishSlug'))) {
                $(this).append('<i class="glyphicon glyphicon-adjust"></i>');
            }
        });

        const dishSlug = $mealContainer.data('dishSlug');
        AdminParticipationEditView.updateParticipantCount(dishSlug, day, data.participantsCount);
    }

    /**
     * Cancels a booked combined meal.
     */
    private cancelCombinedMeal($mealContainer: JQuery): void {
        const url = $mealContainer.attr('data-action-url');
        this.sendRequest(
            url,
            null,
            (data: DeleteResponseData) => AdminParticipationEditView.combinedMealCancelSuccess($mealContainer, data),
            (error: string) => AdminParticipationEditView.toggleFailure(error, MealToggleAction.Cancel, url)
        );
    }

    private static combinedMealCancelSuccess($mealContainer: JQuery, data: DeleteResponseData): void {
        const day = $mealContainer.data('date');
        $(`.table-row.editing .meal-participation[data-date=${day}][data-combined='0'] .glyphicon-adjust`).remove();
        $(`.table-row.editing .meal-participation[data-date=${day}][data-combined='1'] .glyphicon-check`)
            .removeClass('glyphicon-check glyphicon-ok')
            .addClass('glyphicon-unchecked');
        $(`.table-row.editing .meal-participation[data-date=${day}][data-combined='1']`)
            .removeClass('participating')
            .attr('data-action', MealToggleAction.Join)
            .attr('data-action-url', data.url);

        const dishSlug = $mealContainer.data('dishSlug');
        AdminParticipationEditView.updateParticipantCount(dishSlug, day, data.participantsCount);
    }

    /**
     * Error response handler for join and cancel operations on both simple and combined meals.
     */
    private static toggleFailure(error: string, action: MealToggleAction, url: string, payload?: SerializedFormData[]): void {
        let logMsg = `toggle failure, error: ${error}, action: ${action}, url: ${url}, payload: ${payload}`;
        console.log(logMsg);
    }

    /**
     * @param day Date in YYYY-MM-DD format.
     */
    private static getDishesOn(day: string): Dish[]|null {
        let dishes = $('[data-weekly-menu]').data('weeklyMenu');

        if (undefined === dishes[day]) {
            return null;
        }

        return dishes[day]
    }

    /**
     * Extracts simple dish slugs from a given dish collection.
     *
     * @return List of simple dish slugs.
     */
    private static getSimpleDishSlugs(dishes: Dish[]): string[] {
        let slugs: string[] = [];
        for (const dish of dishes) {
            if (dish.isCombined) {
                continue;
            }
            slugs.push(dish.slug);
        }

        return slugs;
    }

    /**
     * Checks weather or not a dish collection contains a dish variation.
     */
    private static dishesContainVariation(dishes: Dish[]): boolean {
        for (const dish of dishes) {
            if (0 < dish.variations.length) {
                return true;
            }
        }

        return false;
    }

    /**
     * Finds a dish, or a dish variation with given slug in a dish collection.
     */
    private static findDish(slug: string, dishes: Dish[]): Dish|DishVariation|null {
        for (const dish of dishes) {
            if (slug === dish.slug) {
                return dish;
            }
            if (0 === dish.variations.length) {
                continue;
            }
            for (const dv of dish.variations) {
                if (slug === dv.slug) {
                    return dv;
                }
            }
        }
    }

    private static updateParticipantCount(dishSlug: string, day: string, count: number): void {
        $(`.meal-count[data-dish-slug=${dishSlug}][data-day=${day}] .count`).text(count);
    }

    private sendRequest(url: string, payload?: SerializedFormData[], successFn?: ReqSuccessFn, failureFn?: ReqFailureFn) {
        $.ajax({
            method: 'POST',
            url: url,
            data: payload,
            dataType: 'json',
            success: successFn,
            error: function (jqXHR, status, error) {
                AjaxErrorHandler.handleError(jqXHR, function(){
                    if (failureFn) {
                        let errMsg = status;
                        if ('' !== error) {
                            errMsg += `, ${error}`;
                        }
                        failureFn(errMsg);
                    }
                });
            }
        });
    }
};
