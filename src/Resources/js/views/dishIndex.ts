import Switchery from 'switchery.js';

export default class DishIndex {
    constructor() {
        DishIndex.initObservers();
    }

    private static initObservers(): void {
        let createDishContainer = document.querySelector('.dish-list .create-form');
        if (null === createDishContainer) {
            console.error('element not found, selector: .dish-list .create-form');
            return;
        }
        let dishTable = document.querySelector('#dish-table tbody');
        if (null === dishTable) {
            console.error('element not found, selector: #dish-table tbody');
            return;
        }

        let observerConfig = {childList: true};
        const observer = new MutationObserver((mutations) => {
            for (const mutation of mutations) {
                mutation.addedNodes.forEach((node) => {
                    if (!(node instanceof HTMLElement)) {
                        return;
                    }
                    let oneServingSizeCheckbox = node.querySelector('form[name=dish] input[type=checkbox]');
                    if (null !== oneServingSizeCheckbox) {
                        new Switchery(oneServingSizeCheckbox, {
                            size: 'small'
                        });
                    }
                });
            }
        });

        observer.observe(createDishContainer, observerConfig);
        observer.observe(dishTable, observerConfig);
    }
}
