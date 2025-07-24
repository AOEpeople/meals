describe('Test GuestEventInvitation', () => {
    beforeEach(() => {
        cy.resetDB();
        cy.loginAs('kochomi');
    });

    it('should copy a invitation link to the clipboard then visit the link and register for an event', () => {
        cy.intercept('GET', '**/api/weeks').as('getWeeks');
        cy.intercept('GET', '**/api/meals/count').as('getDishesCount');
        cy.intercept('GET', '**/api/categories').as('getCategories');
        cy.intercept('GET', '**/api/dishes').as('getDishes');
        cy.intercept('GET', '**/api/events').as('getEvents');
        cy.intercept('GET', '**/api/dashboard').as('getDashboard');
        cy.intercept('GET', '**/api/participations/event/**').as('getParticipants');
        cy.intercept('GET', '**/event/invitation/**').as('getEventInvitation');
        cy.intercept('POST', '**/api/event/invitation/**').as('postEventInvitation');

        cy.visitMeals();
        cy.get('span > a').contains('Mahlzeiten').click();
        cy.wait(['@getWeeks']);

        // Go to the next week
        cy.get('h4').eq(1).contains('Woche').click();
        cy.wait(['@getDishesCount', '@getCategories', '@getDishes']);

        // add an event on monday
        cy.get('input')
            .eq(2)
            .click()
            .parent().parent()
            .find('li').contains('Afterwork')
            .click();

        cy.get('h2').should('contain', 'Woche').click();

        // Save
        cy.contains('input', 'Speichern').click();

        cy.get('[data-cy="msgClose"]').click();

        // find the saved event
        cy.get('input')
            .eq(2)
            .should('have.value', 'Afterwork');

        // go to dashboard
        cy.get('header > nav > div > a > svg').click();
        cy.wait(['@getDashboard', '@getEvents']);

        // confirm event is not joined yet
        cy.get('h2')
            .contains('Nächste Woche')
            .parent()
            .parent()
            .find('span')
            .contains('Montag')
            .parent()
            .parent()
            .find('span')
            .contains('Afterwork')
            .parent()
            .find('svg')
            .eq(0)
            .click();

        cy.wait('@getEventInvitation');
        cy.wait(3000);

        cy.contains('span', 'In die Zwischenablage kopiert!')
            .parent()
            .parent()
            .children()
            .eq(0)
            .then($el => {
                cy.log('Link: ' + $el[0].innerText);
                const link = $el[0].innerText;
                expect(link).to.match(/^(http|https):\/\/(meals.test|localhost)\/guest\/event\/\S*$/);

                cy.clearAllSessionStorage();
                cy.visit(link);

                cy.get('input[placeholder="Vorname"]')
                    .click()
                    .type('John');

                cy.get('input[placeholder="Nachname"]')
                    .click()
                    .type('Doe');

                cy.get('input[placeholder="Betrieb"]')
                    .click()
                    .type('District 17')

                cy.contains('input', 'An Event teilnehmen').click();

                cy.wait('@postEventInvitation');
                cy.get('[data-cy=msgClose]').click();

                cy.visitMeals();
                cy.loginAs('kochomi');

                cy.get('h2')
                    .contains('Nächste Woche')
                    .parent()
                    .parent()
                    .find('span')
                    .contains('Montag')
                    .parent()
                    .parent()
                    .find('span')
                    .contains('Afterwork')
                    .parent()
                    .find('svg')
                    .eq(1)
                    .click();

                    cy.get('div')
                    .find('[data-cy="event-participant"]')
                    .contains('Doe, John (District 17)')
            });
    });
});