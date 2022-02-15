import 'jquery-ui/ui/widgets/dialog';
import {BaseDialog} from "./base-dialog";
import {Dish} from "./combined-meal-service";

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
            buttons: [
                {
                    'text': 'OK',
                    'data-qa': 'ok',
                    'click': this.handleOk.bind(this)
                },
                {
                    'text': 'Cancel',
                    'data-qa': 'cancel',
                    'click': this.handleCancel.bind(this)
                }
            ],
            create: this.handleCreate
        });
    }

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
            return this.getRadioButton(fieldName, d.slug, true, d.title, {wrapper: {'class': 'dish', 'data-qa': 'dish'}});
        }

        let $dish = $('<div class="dish" data-qa="dish"><div class="title">' + d.title + '</div></div>');
        d.variations.forEach((dv, index) => {
            const selected = selectedDishIDs.includes(dv.slug) || (0 === index);
            let $dishVariation = this.getRadioButton(fieldName, dv.slug, selected, dv.title, {
                wrapper: {
                    'class': 'dish-variation',
                    'data-qa': 'dish-variation'
                }
            });
            $dish.append($dishVariation);
        });

        return $dish;
    }

    private getRadioButton(name: string, value: string, selected: boolean, label: string, opts?: RadioElementOptions): JQuery {
        let wrapperExtraAttrs = '';
        if (typeof opts !== 'undefined' && typeof opts.wrapper !== 'undefined') {
            for (const [k, v] of Object.entries(opts.wrapper)) {
                wrapperExtraAttrs += ` ${k}="${v}"`;
            }
        }

        return $(`
            <div${wrapperExtraAttrs}>
                <label>${label}</label>
                <input type="radio" name="${name}" value="${value}"${selected ? ' checked' : ''}>
            </div>
        `);
    }

    private handleOk(): void {
        const $form = this.$dialog.find('form:first');
        this.opts.ok($form.serializeArray());
        this.$dialog.dialog('close');
    }
}

interface CombinedMealDialogOptions {
    ok: (data: any) => void
}

export interface SerializedFormData {
    name: string;
    value: string;
}

interface RadioElementOptions {
    wrapper?: ElementAttributes;
}

interface ElementAttributes {
    [index: string]: string;
}
