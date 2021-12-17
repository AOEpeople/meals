import 'jquery-ui/ui/widgets/dialog';
import DialogUIParams = JQueryUI.DialogUIParams;

export class CombiMealDialog {
    readonly containerID: string = '#combi-meal-selector';
    title: string;
    opts: CombiMealDialogOptions;

    $form: JQuery;
    $dialog: JQuery;

    constructor(private dishes: Dish[], opts: CombiMealDialogOptions) {
        this.title = this.getTitle(dishes);
        this.$form = this.buildForm(dishes);
        this.opts = opts;
    }

    open() {
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

    getTitle(dishes: Dish[]): string {
        return dishes.reduce(function(title: string, dish: Dish) {
            return ('' === title) ? dish.title : `${title} & ${dish.title}`;
        }, '');
    }

    buildForm(dishes: Dish[]): JQuery {
        let $form = $('<form method="post"></form>');
        let $formFields = this.getFormFields(dishes);
        $form.prepend($formFields);

        return $form;
    }

    getFormFields(dishes: Dish[]): JQuery {
        let $dishes = $('<div class="dishes"></div>');

        dishes.forEach((dish, index) => {
            let $dishField = this.getDishField(dish, index);
            $dishes.append($dishField);
        });

        return $dishes;
    }

    getDishField(d: Dish, index: number): JQuery {
        const fieldName = `dishes[${index}]`;

        if (0 === d.variations.length) {
            return this.getRadioButton(fieldName, d.slug, true, d.title, {wrapperClass: 'dish'});
        }

        let $dish = $(`<div class="dish"><div class="title">${d.title}</div></div>`);
        d.variations.forEach((dv, index) => {
            let $dishVariation = this.getRadioButton(fieldName, dv.slug, 0 === index, dv.title, {wrapperClass: 'dish-variation'});
            $dish.append($dishVariation);
        });

        return $dish;
    }

    getRadioButton(name: string, value: string, selected: boolean, label: string, attrs?: ElementAttributes): JQuery {
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

    handleOk(): void {
        let self = this;
        const $form = this.$dialog.find('form:first');
        $.ajax({
            type: 'POST',
            url: '/meal/join',
            data: $form.serialize(),
            success: function (data) {
                self.opts.ok();
            }
        });
        self.$dialog.dialog('close');
    }

    handleCancel(): void {
        this.$dialog.dialog('close');
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

interface CombiMealDialogOptions {
    ok: () => void
}

interface ElementAttributes {
    wrapperClass: string
}
