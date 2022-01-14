import 'jquery-ui/ui/widgets/dialog';
import {BaseDialog} from "./base-dialog";

export class CombinedMealOffersDialog extends BaseDialog {
    private readonly containerID: string = '#combined-meal-selector';
    private readonly title: string;
    private readonly offers: Array<Offer>;
    private opts: CombinedMealDialogOffersOptions;

    private $form: JQuery;

    constructor(title: string, offers: Array<Offer>, opts: CombinedMealDialogOffersOptions) {
        super();
        this.title = title;
        this.offers = offers;
        this.$form = this.buildForm(offers);
        this.opts = opts;
    }

    public open(): void {
        this.$dialog = $(this.containerID).empty().append(this.$form).dialog({
            modal: true,
            width: 500,
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

    private buildForm(offers: Array<Offer>): JQuery {
        let $form = $('<form method="post"></form>');
        let $formFields = this.getFormFields(offers);
        $form.prepend($formFields);
        return $form;
    }

    private getFormFields(offers: Array<Offer>): JQuery {
        let $offers = $('<div class="offers"></div>');

        let selected = true;
        for (let offer of offers) {
            let $offerField = this.getOfferField(offer, selected);
            $offers.append($offerField);
            selected = false;
        }

        return $offers;
    }

    private getOfferField(offer: Offer, selected: boolean): JQuery {
        const fieldName = 'offer';
        let dishTitles = [];
        for (let dishInfo of offer.dishes) {
            dishTitles.push(dishInfo.title);
        }
        let title = dishTitles.join(', ') + ' (' + offer.count + ')';

        return this.getRadioButton(fieldName, offer.id, selected, title, {wrapperClass: 'offer'});
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
        let formData = $form.serializeArray();
        if (formData.length > 0) {
            let offerChoice;
            formData.forEach(formEntry => {
                if ('offer' === formEntry.name) {
                    offerChoice = formEntry.value;
                    return;
                }
            });

            let dishSlugs = [];
            for (let offer of this.offers) {
                if (offer.id === offerChoice) {
                    let index = 0;
                    for (let dish of offer.dishes) {
                        dishSlugs.push({
                            name: 'dishes[' + index + ']',
                            value: dish.slug
                        });
                        index++;
                    }
                    break;
                }
            }
            this.opts.ok(dishSlugs);
        } else {
            console.log("Error: Form is empty");
        }

        this.$dialog.dialog('close');
    }
}

export interface DishInfo {
    slug: string
    title: string
}

export interface Offer {
    id: string
    count: number
    dishes: Array<DishInfo>
}

interface CombinedMealDialogOffersOptions {
    ok: (data: any) => void
}

interface ElementAttributes {
    wrapperClass: string
}
