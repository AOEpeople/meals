import 'jquery-ui/ui/widgets/dialog';
import {BaseDialog} from "./base-dialog";

export class CombinedMealDialog extends BaseDialog {
    private readonly containerID: string = '#combined-meal-selector';
    private readonly title: string;
    private readonly slotSlug: string;
    private opts: CombinedMealDialogOptions;

    private $form: JQuery;

    constructor(
        title: string,
        dishes: Dish[],
        selectedDishIDs: string[],
        slotSlug: string,
        opts: CombinedMealDialogOptions
    ) {
        super();
        this.title = title;
        this.slotSlug = slotSlug;
        this.$form = this.buildForm(dishes, selectedDishIDs, this.slotSlug);
        this.opts = opts;
    }

    public open(): void {
        this.$dialog = $(this.containerID).empty().append(this.$form).dialog({
            modal: true,
            width: 400,
            maxWidth: 500,
            title: this.title,
            draggable: false,
            buttons: {
                'OK': this.handleOk.bind(this),
                'Cancel': this.handleCancel.bind(this)
            },
            create: this.handleCreate
        });
    }

    // private getTitle(dishes: Dish[]): string {
    //     return dishes.reduce(function (title: string, dish: Dish) {
    //         return ('' === title) ? dish.title : `${title} & ${dish.title}`;
    //     }, '');
    // }

    private buildForm(dishes: Dish[], selectedDishIDs: string[], slotSlug: string): JQuery {
        let $form = $('<form method="post"></form>');
        let $formFields = this.getFormFields(dishes, selectedDishIDs);
        $form.prepend($formFields);
        let $slotSlugField = '<input type="hidden" name="slot" value="' + slotSlug + '">';
        $form.prepend($slotSlugField);

        return $form;
    }

    private getFormFields(dishes: Dish[], selectedDishIDs: string[]): JQuery {
        let $dishes = $('<div class="dishes"></div>');

        dishes.forEach((dish, index) => {
            let $dishField = this.getDishField(dish, index, selectedDishIDs);
            $dishes.append($dishField);
        });

        return $dishes;
    }

    private getDishField(d: Dish, index: number, selectedDishIDs: string[]): JQuery {
        const fieldName = `dishes[${index}]`;

        if (0 === d.variations.length) {
            return this.getRadioButton(fieldName, d.slug, true, d.title, {wrapperClass: 'dish'});
        }

        let $dish = $('<div class="dish"><div class="title">' + d.title + '</div></div>');
        d.variations.forEach((dv, index) => {
            const selected = selectedDishIDs.includes(dv.slug) || (0 === index);
            let $dishVariation = this.getRadioButton(fieldName, dv.slug, selected, dv.title, {wrapperClass: 'dish-variation'});
            $dish.append($dishVariation);
        });

        return $dish;
    }

    private getRadioButton(name: string, value: string, selected: boolean, label: string, attrs?: ElementAttributes): JQuery {
        let attributes = Object.assign({
            wrapperClass: 'wrapper'
        }, attrs || {});

        return $(`
            <div class="${attributes.wrapperClass}">
                <label for="">${label}</label>
                <input type="radio" name="${name}" value="${value}" ${selected ? ' checked' : ''}>
            </div>
        `);
    }

    private handleOk(): void {
        const $form = this.$dialog.find('form:first');
        this.opts.ok($form.serializeArray());
        this.$dialog.dialog('close');
    }
}

export interface Dish extends DishVariation {
    variations: DishVariation[]
}

export interface DishVariation {
    title: string
    slug: string
}

interface CombinedMealDialogOptions {
    ok: (data: any) => void
}

interface ElementAttributes {
    wrapperClass: string
}
