describe('Test CashRegister', () => {
    beforeEach(() => {
        cy.resetDB();
        cy.loginAs('kochomi');
        cy.visitMeals();

        // spy on the request to the backend to wait for them to resolve before testing
        cy.intercept('GET', '**/api/costs').as('getCosts');
        cy.intercept('GET', '**/api/accounting/book').as('getBook');
    });

    it('should be able to navigate to the cash register', () => {
        cy.visit('/costs');

        cy.wait('@getCosts');

        // Hide Symphony's toolbar
        cy.get('a[class="hide-button"]').click();

        cy.get('span').contains('Kasse').click();

        cy.wait('@getBook');

        cy.get('table:visible').should('have.length', 2);
        cy.get('tr').eq(1).find('td').should('have.length', 3);

        cy.get('th').contains('Name').should('exist');
        cy.get('th').contains('Betrag').should('exist');
        cy.get('th').contains('Art').should('exist');
        cy.get('td').contains('Gesamt').should('exist');
    });
});