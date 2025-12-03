describe('Test the DebtPopup', () => {
    beforeEach(() => {
        cy.setCookie('locale', 'de');
        cy.loginAs('kochomi');

        // spy on the request to the backend to wait for them to resolve before testing
        cy.intercept('GET', '**/api/user', { fixture: 'userNegBalance.json', statusCode: 200 }).as('getUser');
    });

    it('should show a DebtPopUp on the initial load', () => {
        cy.visitMeals();
        cy.wait('@getUser');

        cy.get('[data-cy="debtText"]').contains(/Kontostand von -40,10 € habe/);

        cy.get('div').contains('Ok, kapiert!').click();

        cy.get('[data-cy="debtText"]').should('not.exist');
    });

    it('should show a DebtPopUp every time a new route is loaded except when it is the balance route', () => {
        cy.visitMeals();

        cy.wait('@getUser');

        cy.wait(100);
        cy.log('first');
        cy.get('[data-cy="debtText"]').contains(/Kontostand von -40,10 € habe/);
        cy.get('div').contains('Ok, kapiert!').click();

        cy.wait(100);
        cy.log('second');
        cy.get('span > a').contains('Mahlzeiten').click();
        cy.get('[data-cy="debtText"]').contains(/Kontostand von -40,10 € habe/);
        cy.get('div').contains('Ok, kapiert!').click();

        cy.wait(100);
        cy.log('third');
        cy.get('span > a').contains('Gerichte').click();
        cy.get('[data-cy="debtText"]').contains(/Kontostand von -40,10 € habe/);
        cy.get('div').contains('Ok, kapiert!').click();

        cy.wait(100);
        cy.log('fourth');
        cy.get('span > a').contains('Kategorien').click();
        cy.get('[data-cy="debtText"]').contains(/Kontostand von -40,10 € habe/);
        cy.get('div').contains('Ok, kapiert!').click();

        cy.wait(100);
        cy.log('fifth');
        cy.get('span > a').contains('Kosten').click();
        cy.get('[data-cy="debtText"]').contains(/Kontostand von -40,10 € habe/);
        cy.get('div').contains('Jetzt bezahlen').click();

        cy.location().should((location) => {
            expect(location.pathname).to.eq('/balance');
        });
    });
});