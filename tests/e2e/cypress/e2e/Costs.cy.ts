describe('Test Cost View', () => {
    beforeEach(() => {
        cy.setCookie('locale', 'de');
        cy.loginAs('kochomi');
        cy.visitMeals();
        cy.resetDB();

        // spy on the request to the backend to wait for them to resolve before testing
        cy.intercept('GET', '**/api/costs').as('getCosts');
        cy.intercept('POST', '**/api/costs/hideuser/**').as('hideUser');
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

        cy.get('tr').should('have.length', 7);

        cy.get('label').contains('Ausgeblendete Benutzer anzeigen').click();

        cy.get('tr').should('have.length', 8);
    });
});