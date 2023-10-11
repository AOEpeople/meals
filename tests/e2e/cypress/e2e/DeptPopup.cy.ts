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

        cy.contains('p', /Kontostand von -50,10 € habe/);

        cy.get('div').contains('Ok, kapiert!').click();

        cy.get('p').contains(/Kontostand von -50,10 € habe/).should('not.be.visible');
    });

    it('should show a DebtPopUp every time a new route is loaded except when it is the balance route', () => {
        cy.visitMeals();

        cy.wait('@getUser');

        cy.contains('p', /Kontostand von -50,10 € habe/);
        cy.get('div').contains('Ok, kapiert!').click();

        cy.visit('/weeks');
        cy.contains('p', /Kontostand von -50,10 € habe/);
        cy.get('div').contains('Ok, kapiert!').click();

        cy.visit('/dishes');
        cy.contains('p', /Kontostand von -50,10 € habe/);
        cy.get('div').contains('Ok, kapiert!').click();

        cy.visit('/categories');
        cy.contains('p', /Kontostand von -50,10 € habe/);
        cy.get('div').contains('Ok, kapiert!').click();

        cy.visit('/costs');
        cy.contains('p', /Kontostand von -50,10 € habe/);
        cy.get('div').contains('Jetzt bezahlen').click();

        cy.get('p').contains(/Kontostand von -50,10 € habe/).should('not.be.visible');

        cy.location().should((location) => {
            expect(location.pathname).to.eq('/balance');
        });
    });
});