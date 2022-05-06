import {CombinedMealDialog, SerializedFormData} from "../modules/combined-meal-dialog";
import {Dish, DishVariation} from "../modules/combined-meal-service";

interface DeleteResponseData {
    participantsCount: number;
    url: string;
    actionText: string;
    available: boolean;
}

interface JoinResponseData {
    id: number,
    participantsCount: number;
    url: string;
    actionText: string;
    bookedDishSlugs: string[];
    slot: string;
    available: boolean;
}

// function type for request success/failure handlers
type ReqFailureFn = (error: string) => void;
type ReqSuccessFn = (data: unknown) => void;

enum MealToggleAction {
    Join = 'join',
    Quit = 'delete'
}

export default class AdminParticipationEditView {
    constructor() {
        this.initEvents();
    }

    private initEvents(): void {
        $('.table-content')
            // edit meal participation event
            .on(
                'click',
                '.table-row .table-data.text',
                this.handleEditMealParticipation.bind(this)
            )
            // simple meal participation toggle event
            .on(
                'click',
                '.table-row.editing .meal-participation[data-combined=0]',
                this.handleSimpleMealToggleParticipation.bind(this)
            )
            // combined meal participation toggle event
            .on(
                'click',
                '.table-row.editing .meal-participation[data-combined=1]',
                this.handleCombinedMealToggleParticipation.bind(this)
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

        // select user row was in edit mode, and has been reset; do nothing
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

    private handleSimpleMealToggleParticipation(event: JQuery.TriggeredEvent): void {
        let $mealContainer = $(event.target).closest('[data-combined]');
        const action = $mealContainer.attr('data-action') as MealToggleAction;
        const url = $mealContainer.attr('data-action-url');
        this.sendRequest(
            url,
            null,
            function(data: JoinResponseData){
                const nextAction = action === MealToggleAction.Quit ? MealToggleAction.Join : MealToggleAction.Quit;
                $mealContainer
                    .attr('data-action-url', data.url)
                    .attr('data-action', nextAction)
                    .toggleClass('participating')
                    .find('i:first')
                    .toggleClass('glyphicon-check glyphicon-unchecked');
            },
            (error: string) => this.toggleFailure(error, action, url)
        );
    }

    private getDishesOn(day: string): Dish[]|null {
        let dishes = $('[data-weekly-menu]').data('weeklyMenu');

        if (undefined === dishes[day]) {
            return null;
        }

        return dishes[day]
    }

    private findDish(slug: string, dishes: Dish[]): Dish|DishVariation|null {
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

    private dishesContainVariation(dishes: Dish[]): boolean {
        for (const dish of dishes) {
            if (0 < dish.variations.length) {
                return true;
            }
        }

        return false;
    }

    private getSimpleDishSlugs(dishes: Dish[]): string[] {
        let slugs: string[] = [];
        for (const dish of dishes) {
            if (dish.isCombined) {
                continue;
            }
            slugs.push(dish.slug);
        }

        return slugs;
    }

    private handleCombinedMealToggleParticipation(event: JQuery.TriggeredEvent): void {
        let $mealContainer = $(event.target).closest('[data-combined]');
        const action = $mealContainer.attr('data-action');

        if (MealToggleAction.Quit === action) {
            this.quitMeal($mealContainer);
            return;
        }

        this.joinCombinedMeal($mealContainer);
    }

    private joinCombinedMeal($mealContainer: JQuery): void {
        const day = $mealContainer.data('date');
        let dishes = this.getDishesOn(day);
        const dishSlug = $mealContainer.data('dishSlug');
        const dish = this.findDish(dishSlug, dishes);

        if (null === dish) {
            console.log(`dish not found, slug: ${dishSlug}`);
            return;
        }

        if (this.dishesContainVariation(dishes)) {
            this.joinCombinedMealWithVariations($mealContainer);
        } else {
            this.joinCombinedMealWithoutVariations($mealContainer);
        }
    }

    private joinCombinedMealWithVariations($mealContainer: JQuery): void {
        let self = this;
        const day = $mealContainer.data('date');
        let dishes = this.getDishesOn(day);
        const dishSlug = $mealContainer.data('dishSlug');
        const dish = this.findDish(dishSlug, dishes);
        const url = $mealContainer.attr('data-action-url');
        let cmd = new CombinedMealDialog(
            dish.title,
            dishes,
            [],
            null,
            {
                ok: function (payload: SerializedFormData[]) {
                    self.sendRequest(
                        url,
                        payload,
                        (data: JoinResponseData) => self.combinedMealJoinSuccess($mealContainer, data),
                        (error: string) => self.toggleFailure(error, MealToggleAction.Join, url)
                    );
                }
            }
        );
        cmd.open();
    }

    private joinCombinedMealWithoutVariations($mealContainer: JQuery): void {
        let self = this;
        const day = $mealContainer.data('date');
        let dishes = this.getDishesOn(day);
        const dishSlugs = this.getSimpleDishSlugs(dishes);

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
            (data: JoinResponseData) => self.combinedMealJoinSuccess($mealContainer, data),
            (error: string) => self.toggleFailure(error, MealToggleAction.Join, url)
        );
    }

    private quitMeal($mealContainer: JQuery): void {
        let self = this;
        const url = $mealContainer.attr('data-action-url');
        this.sendRequest(
            url,
            null,
            (data: DeleteResponseData) => self.combinedMealQuitSuccess($mealContainer, data),
            (error: string) => self.toggleFailure(error, MealToggleAction.Quit, url)
        );
    }

    private combinedMealJoinSuccess($mealContainer: JQuery, data: JoinResponseData): void {
        $mealContainer
            .addClass('participating')
            .attr('data-action', MealToggleAction.Quit)
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
    }

    private combinedMealQuitSuccess($mealContainer: JQuery, data: DeleteResponseData): void {
        const day = $mealContainer.data('date');
        $(`.table-row.editing .meal-participation[data-date=${day}][data-combined='0'] .glyphicon-adjust`).remove();
        $(`.table-row.editing .meal-participation[data-date=${day}][data-combined='1'] .glyphicon-check`)
            .removeClass('glyphicon-check glyphicon-ok')
            .addClass('glyphicon-unchecked');
        $(`.table-row.editing .meal-participation[data-date=${day}][data-combined='1']`)
            .removeClass('participating')
            .attr('data-action', MealToggleAction.Join)
            .attr('data-action-url', data.url);
    }

    private toggleFailure(error: string, action: MealToggleAction, url: string, payload?: SerializedFormData[]): void {
        let logMsg = `toggle failure, error: ${error}, action: ${action}, url: ${url}, payload: ${payload}`;
        console.log(logMsg);
    }

    private sendRequest(url: string, payload?: SerializedFormData[], successFn?: ReqSuccessFn, failureFn?: ReqFailureFn) {
        $.ajax({
            method: 'POST',
            url: url,
            data: payload,
            dataType: 'json',
            success: successFn,
            error: function (xhr, status, error) {
                if (failureFn) {
                    let errMsg = status;
                    if ('' !== error) {
                        errMsg += `, ${error}`;
                    }
                    failureFn(errMsg);
                }
            }
        });
    }
};
