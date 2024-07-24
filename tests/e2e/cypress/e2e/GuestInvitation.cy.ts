describe('Test GuestEventInvitation', () => {
    beforeEach(() => {
        cy.resetDB();
        cy.loginAs('kochomi');
    });

    it('should copy an invitation link to the clipboard then visit the link and register for a meal', () => {
        cy.intercept('GET', '**/api/weeks').as('getWeeks');
        cy.intercept('GET', '**/api/meals/count').as('getDishesCount');
        cy.intercept('GET', '**/api/categories').as('getCategories');
        cy.intercept('GET', '**/api/dishes').as('getDishes');
        cy.intercept('GET', '**/api/events').as('getEvents');
        cy.intercept('GET', '**/api/dashboard').as('getDashboard');
        cy.intercept('GET', '**/api/participations/event/**').as('getParticipants');
        cy.intercept('GET', '**/api/guest-invitation-*').as('getInvitation');
        cy.intercept('GET', '**/menu/*/new-guest-invitation').as('getInvitationLink');
        cy.intercept('POST', '**/api/guest/meal/participation').as('postInvitation');

        cy.visitMeals();

        cy.get('h2')
            .contains('Nächste Woche')
            .parent()
            .parent()
            .find('span')
            .contains('Montag')
            .parent()
            .find('svg')
            .eq(1)
            .click();

            cy.wait('@getInvitationLink');
            cy.wait(3000);

            cy.contains('span', 'In die Zwischenablage kopiert!')
                .parent()
                .parent()
                .children()
                .eq(0)
                .then($el => {
                    cy.log('Link: ' + $el[0].innerText);
                    const link = $el[0].innerText;
                    expect(link).to.match(/^(http|https):\/\/(meals.test|localhost)\/guest\/\S*$/);

                    cy.clearAllSessionStorage();
                    cy.visit(link);

                    cy.get('input[placeholder="Dein Vorname"]')
                        .click()
                        .type('John');

                    cy.get('input[placeholder="Dein Nachname"]')
                        .click()
                        .type('Doe');

                    cy.get('input[placeholder="Dein Betrieb"]')
                        .click()
                        .type('District 17');

                    cy.get('span[data-cy="guest-checkbox"]')
                        .first()
                        .click();

                    cy.contains('button', 'Gerichte buchen')
                        .click();

                    cy.wait('@postInvitation');

                    cy.visitMeals();
                    cy.loginAs('kochomi');

                    cy.get('h2')
                        .contains('Nächste Woche')
                        .parent()
                        .parent()
                        .find('span')
                        .contains('Montag')
                        .parent()
                        .find('svg')
                        .eq(0)
                        .click();

                    cy.get('h2')
                        .contains('Teilnahmen am')
                        .parent()
                        .parent()
                        .find('table')
                        .find('td')
                        .contains('John Doe');
                });
    });
});