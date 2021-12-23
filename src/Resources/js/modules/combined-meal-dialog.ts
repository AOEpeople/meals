import 'jquery-ui/ui/widgets/dialog';

export class CombinedMealDialog {
    private readonly containerID: string = '#combined-meal-selector';
    private readonly title: string;
    private readonly slotSlug: string;
    private readonly path: string;
    private opts: CombinedMealDialogOptions;

    private $form: JQuery;
    private $dialog: JQuery;

    constructor(private dishes: Dish[], slotSlug: string, path: string, opts: CombinedMealDialogOptions) {
        this.title = this.getTitle(dishes);
        this.path = path;
        this.slotSlug = slotSlug;
        this.$form = this.buildForm(dishes, this.slotSlug);
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

    private getTitle(dishes: Dish[]): string {
        return dishes.reduce(function(title: string, dish: Dish) {
            return ('' === title) ? dish.title : `${title} & ${dish.title}`;
        }, '');
    }

    private buildForm(dishes: Dish[], slotSlug: string): JQuery {
        let $form = $('<form method="post"></form>');
        let $formFields = this.getFormFields(dishes);
        $form.prepend($formFields);
        let $slotSlugField = '<input type="hidden" name="slug" value="' + slotSlug + '">';
        $form.prepend($slotSlugField);

        return $form;
    }

    private getFormFields(dishes: Dish[]): JQuery {
        let $dishes = $('<div class="dishes"></div>');

        dishes.forEach((dish, index) => {
            let $dishField = this.getDishField(dish, index);
            $dishes.append($dishField);
        });

        return $dishes;
    }

    private getDishField(d: Dish, index: number): JQuery {
        const fieldName = `dishes[${index}]`;

        if (0 === d.variations.length) {
            return this.getRadioButton(fieldName, d.slug, true, d.title, {wrapperClass: 'dish'});
        }

        let $dish = $('<div class="dish"><div class="title">' + d.title + '</div></div>');
        d.variations.forEach((dv, index) => {
            let $dishVariation = this.getRadioButton(fieldName, dv.slug, 0 === index, dv.title, {wrapperClass: 'dish-variation'});
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
        let self = this;
        const $form = this.$dialog.find('form:first');
        if (self.opts.ajax) {
            $.ajax({
                type: 'POST',
                url: this.path,
                data: $form.serialize(),
                success: function (data) {
                    console.log(data);
                    self.opts.ok(data);
                }
            });
        } else {
            let formArray: any = $form.serializeArray();
            self.opts.ok(formArray);
        }
        self.$dialog.dialog('close');
    }

    private handleCancel(): void {
        this.$dialog.dialog('close');
    }

    private handleCreate(): void {
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

interface CombinedMealDialogOptions {
    ajax: boolean
    ok: (data: []) => void
}

interface ElementAttributes {
    wrapperClass: string
}
