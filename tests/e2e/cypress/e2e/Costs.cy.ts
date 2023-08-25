describe('Test Cost View', () => {
    beforeEach(() => {
        cy.resetDB();
        cy.setCookie('locale', 'de');
        cy.loginAs('kochomi');
        cy.visitMeals();

        // spy on the request to the backend to wait for them to resolve before testing
        cy.intercept('GET', '**/api/costs').as('getCosts');
        cy.intercept('POST', '**/api/costs/hideuser/**').as('hideUser');
        cy.intercept('POST', '**/api/costs/settlement/confirm/**').as('confirmSettlement');
    });

    it('should visit /costs and filter for a user and hide it', () => {
        cy.visit('/costs');

        cy.wait('@getCosts');

        // Hide Symphony's toolbar
        cy.get('a[class="hide-button"]').click();

        cy.get('h2').contains('Liste der Kosten');

        cy.get('input[placeholder="Benutzer filtern"]').type('Admin');

        // find the test user and assert that the table has the right dimensions
        cy.get('tr')
            .eq(1)
            .find('td')
            .eq(0)
            .contains('Meals, Admin')
            .parent()
            .find('td')
            .should('have.length', 8);

        cy.get('tr')
            .eq(1)
            .find('td')
            .eq(7)
            .find('button')
            .first()
            .click();

        cy.wait('@hideUser');

        cy.get('td')
            .contains('Meals, Admin')
            .should('not.exist');

        cy.get('input').first().clear();

        cy.get('tr:visible').should('have.length', 7);

        cy.get('label').contains('Ausgeblendete Benutzer anzeigen').click();

        cy.get('tr:visible').should('have.length', 8);
    });

    it('should be able to settle an account and add balance to it', () => {
        cy.visit('/costs');

        cy.wait('@getCosts');

        // Hide Symphony's toolbar
        cy.get('a[class="hide-button"]').click();

        cy.get('h2').contains('Liste der Kosten');

        cy.get('input[placeholder="Benutzer filtern"]').type('Alice');

        cy.get('tr')
            .eq(1)
            .find('td')
            .eq(0)
            .contains('Meals, Alice')
            .parent()
            .find('td')
            .should('have.length', 8);

        cy.get('tr')
            .eq(1)
            .find('td')
            .eq(7)
            .find('button')
            .eq(1)
            .click();

        cy.get('form')
            .find('input')
            .first()
            .clear()
            .type('1234');

        cy.get('form')
            .find('input[value="Speichern"]')
            .click();

        cy.get('tr')
            .eq(1)
            .find('td')
            .eq(7)
            .find('button')
            .last()
            .click();

        cy.get('div').contains('Fortfahren').click();

        cy.visitSettlementLinkFromMail();
        cy.get('div').contains('Bestätigen').click();
        cy.wait('@confirmSettlement');

        cy.visit('/costs');
        cy.wait('@getCosts');

        cy.get('input[placeholder="Benutzer filtern"]').type('Alice');

        cy.get('tr')
            .eq(1)
            .find('td')
            .eq(6)
            .contains('0,00 €');

        cy.get('tr')
            .eq(1)
            .find('td')
            .eq(7)
            .find('button')
            .last()
            .click();

        cy.get('form')
            .find('input')
            .first()
            .clear()
            .type('147');

        cy.get('form')
            .find('input[value="Speichern"]')
            .click();

        cy.get('tr')
            .eq(1)
            .find('td')
            .eq(6)
            .contains('147,00 €');
    });
});