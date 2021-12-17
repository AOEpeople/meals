import 'jquery-ui/ui/widgets/dialog';
import DialogUIParams = JQueryUI.DialogUIParams;

export class CombiMealDialog  {
    title: string;
    $form: JQuery;
    $dialog: JQuery;

    constructor(private dishes: Dish[]) {
        this.title = dishes.reduce(function(title: string, dish: Dish) {
            if ('' === title) {
                return dish.title;
            }

            return `${title} & ${dish.title}`;
        }, '');
        this.$form = this.buildForm(dishes);
    }

    open() {
        this.$dialog = $('#combi-meal-selector').empty().append(this.$form).dialog({
            modal: true,
            width: 400,
            maxWidth: 500,
            title: this.title,
            draggable: false,
            buttons: {
                'OK': this.handleOk,
                'Cancel': this.handleCancel
            },
            create: this.handleCreate
        });
    }

    buildForm(dishes: Dish[]): JQuery {
        let $form = $('<form method="post"></form>');
        let $formFields = this.getFormFields(dishes);
        $form.prepend($formFields);

        return $form;
    }

    getFormFields(dishes: Dish[]): JQuery {
        let $dishes = $('<div class="dishes"></div>');

        dishes.forEach((dish) => {
            let $dishField = this.getDishField(dish);
            $dishes.append($dishField);
        });

        return $dishes;
    }

    getDishField(dish: Dish): JQuery {
        let $dish = $('<div class="dish"></div>');

        if (0 === dish.variations.length) {
            $dish.append(`<label for="">${dish.title}</label><input type="radio" name="${dish.slug}" value="1" checked>`);
            return $dish;
        }

        $dish.append(`<div class="title">${dish.title}</div>`);
        dish.variations.forEach((dishVariation, index) => {
            let $dishVariation = this.getDishVariationField(dishVariation, 0 === index);
            $dish.append($dishVariation);
        });

        return $dish;
    }

    getDishVariationField(dv: DishVariation, checked: boolean = false): JQuery {
        return $(`
            <div class="dish-variation">
                <label for="">${dv.title}</label>
                <input type="radio" name="${dv.slug}" value="1" ${checked ? ' checked' : ''}>
            </div>
        `);
    }

    handleOk(): void {
        const $dialog = $(this);
        const $form = $dialog.find('form:first');
        $.ajax({
            type: 'POST',
            url: '/meal/join',
            data: $form.serialize(),
            success: function (data) {
                //
            }
        });
        $dialog.dialog('close');
    }

    handleCancel(): void {
        $(this).dialog('close');
    }

    handleCreate(event: JQueryEventObject, ui: DialogUIParams): void {
        let $widget = $(this).dialog('widget');
        $widget.removeClass('ui-corner-all');
        $widget.find('.ui-dialog-titlebar-close').remove();
        $widget.find('.ui-button').removeClass('ui-corner-all');
    }
}

interface Dish extends DishVariation {
    variations: DishVariation[]
}

interface DishVariation {
    title: string
    slug: string
}
