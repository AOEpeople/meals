// import ModalDialog = require('./modal-dialog');
import 'jquery-ui/ui/widgets/dialog';

export class CombiMealDialog  {
    $content: JQuery;

    constructor(private dishes: Dish[]) {
        this.$content = this.getDishCollectionNode(dishes);
    }

    open() {
        $('#combi-meal-selector').empty().append(this.$content).dialog({
            modal: true,
            buttons: {
                'OK': function () {
                    $(this).dialog('close');
                },
                'Cancel': function () {
                    $(this).dialog('close');
                }
            }
        });
    }

    getDishCollectionNode(dishes: Dish[]): JQuery {
        let $collectionNode = $('<div class="dish-collection"></div>');

        dishes.forEach((dish) => {
            let $dishNode = this.getDishNode(dish);
            $collectionNode.append($dishNode);
        });

        return $collectionNode;
    }

    getDishNode(dish: Dish): JQuery {
        let $dish = $('<div class="dish"></div>');

        if (0 === dish.variations.length) {
            $dish.append('<label for="">' +  dish.title + '</label><input type="radio">')
            return $dish;
        }

        $dish.append('<div class="title">' + dish.title + '</div>')
        dish.variations.forEach((dishVariation) => {
            let $dishVariation = this.getDishVariationNode(dishVariation);
            $dish.append($dishVariation);
        });

        return $dish;
    }

    getDishVariationNode(dishVariation: DishVariation): JQuery {
        return $('<div class="dish-variation">' + dishVariation.title + '<input type="radio"></div>');
    }
}

interface Dish {
    title: string
    variations: DishVariation[]
}

interface DishVariation {
    title: string
}
