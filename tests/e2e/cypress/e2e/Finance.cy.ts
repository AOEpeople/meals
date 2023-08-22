describe('Test Cost View', () => {
    beforeEach(() => {
        cy.setCookie('locale', 'de');
        cy.loginAs('kochomi');
        cy.visitMeals();
        cy.resetDB();

        // spy on the request to the backend to wait for them to resolve before testing
        cy.intercept('GET', '**/api/accounting/book/finance/list').as('getFinances');
        cy.intercept('GET', '**/api/accounting/book/finance/list/**').as('getFilteredFinances');
    });

    it('should visit /finance', () => {
        cy.visit('/finance');

        cy.wait('@getFinances');

        // Hide Symphony's toolbar
        cy.get('a[class="hide-button"]').click();

        cy.get('table:visible').should('have.length', 2);
        cy.get('tr').eq(1).find('td').should('have.length', 3);

        cy.get('th').contains('Datum').should('exist');
        cy.get('th').contains('Name').should('exist');
        cy.get('th').contains('Betrag').should('exist');
        cy.get('th').contains('Tagesabschluss').should('exist');
    });

    it('should be able to get finances for a specific date', () => {
        cy.visit('/finance');

        cy.wait('@getFinances');

        // Hide Symphony's toolbar
        cy.get('a[class="hide-button"]').click();

        cy.get('input').as('range').click();
        cy.get('.dp__calendar_item .dp__range_start').click();
        cy.get('.dp__calendar_item').contains('20').click();

        cy.wait('@getFilteredFinances');

        cy.get('h1').should("contain.text", '01');
        cy.get('h1').should("contain.text", '20');
    });
});
