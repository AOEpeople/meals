describe('Test Price View', () => {
    beforeEach(() => {
        cy.resetDB();
        cy.loginAs('kochomi');
        cy.visitMeals();

        cy.intercept('GET', '**/api/prices').as('getPrices');
        cy.intercept('POST', '**/api/price').as('createPrice');
        cy.intercept('PUT', '**/api/price/2026').as('updatePrice');
        cy.intercept('DELETE', '**/api/price/2025').as('deletePrice');
    });

    it('should visit /prices and create new price', () => {
        cy.get('span > a').contains('Preise').click();
        cy.wait('@getPrices');
        cy.get('#headlessui-popover-button-v-2').click();
        cy.get('#create-price-per-dish-field')
            .clear()
            .type('10');
        cy.get('#create-price-per-combined-dishes-field')
            .clear()
            .type('12');
        cy.get('input[type="submit"]').click();
        cy.wait('@createPrice');
        cy.get('td')
            .contains('10,00 €')
            .should('exist')
        cy.get('td')
            .contains('12,00 €')
            .should('exist')
    });

    it('should visit /prices and create new price below min price', () => {
        cy.get('span > a').contains('Preise').click();
        cy.wait('@getPrices');
        cy.get('#headlessui-popover-button-v-2').click();
        cy.get('#create-price-per-dish-field')
            .clear()
            .type('2');
        cy.get('#create-price-per-combined-dishes-field')
            .clear()
            .type('4');
        cy.get('input[type="submit"]').click();
        cy.get('td')
            .contains('2,00 €')
            .should('not.exist')
        cy.get('td')
            .contains('4,00 €')
            .should('not.exist')
    });

    it('should visit /prices and update new created price', () => {
        cy.get('span > a').contains('Preise').click();
        cy.wait('@getPrices');
        cy.get('#headlessui-popover-button-v-2').click();
        cy.get('#create-price-per-dish-field')
            .clear()
            .type('10');
        cy.get('#create-price-per-combined-dishes-field')
            .clear()
            .type('12');
        cy.get('input[type="submit"]').click();
        cy.wait('@createPrice');
        cy.get('#edit-2026-price-button').click();
        cy.get('#edit-price-per-dish-field')
            .clear()
            .type('11');
        cy.get('#edit-price-per-combined-dishes-field')
            .clear()
            .type('13');
        cy.get('input[type="submit"]').click();
        cy.wait('@updatePrice');
        cy.get('td')
            .contains('11,00 €')
            .should('exist');
        cy.get('td')
            .contains('13,00 €')
            .should('exist');
    });

    it('should visit /prices and update new created price with higher than max price', () => {
        cy.get('span > a').contains('Preise').click();
        cy.wait('@getPrices');

        cy.get('#headlessui-popover-button-v-2').click();
        cy.get('#create-price-per-dish-field')
            .clear()
            .type('10');
        cy.get('#create-price-per-combined-dishes-field')
            .clear()
            .type('12');
        cy.get('input[type="submit"]').click();
        cy.wait('@createPrice');

        cy.get('#create-price-per-dish-field').should('not.exist')
        cy.get('#headlessui-popover-button-v-2').click();
        cy.get('#create-price-per-dish-field')
            .clear()
            .type('11');
        cy.get('#create-price-per-combined-dishes-field')
            .clear()
            .type('13');
        cy.get('input[type="submit"]').click();
        cy.wait('@createPrice');

        cy.get('#edit-2026-price-button').click();
        cy.get('#edit-price-per-dish-field')
            .clear()
            .type('13');
        cy.get('#edit-price-per-combined-dishes-field')
            .clear()
            .type('15');
        cy.get('input[type="submit"]').click();
        cy.get('td')
            .contains('14,00 €')
            .should('not.exist');
        cy.get('td')
            .contains('16,00 €')
            .should('not.exist');
    });

    it('should visit /prices and delete last new created price', () => {
        cy.get('span > a').contains('Preise').click();
        cy.wait('@getPrices');
        cy.get('#headlessui-popover-button-v-2').click();
        cy.get('#create-price-per-dish-field')
            .clear()
            .type('10');
        cy.get('#create-price-per-combined-dishes-field')
            .clear()
            .type('12');
        cy.get('input[type="submit"]').click();
        cy.wait('@createPrice');
        cy.get('#create-price-per-dish-field').should('not.exist')
        cy.get('#headlessui-popover-button-v-2').click();
        cy.get('#create-price-per-dish-field')
            .clear()
            .type('11');
        cy.get('#create-price-per-combined-dishes-field')
            .clear()
            .type('13');
        cy.get('input[type="submit"]').click();
        cy.wait('@createPrice');

        cy.get('#delete-2027-price-button').click();

        cy.get('td')
            .contains('11,00 €')
            .should('not.exist')
        cy.get('td')
            .contains('13,00 €')
            .should('not.exist')
    });
});