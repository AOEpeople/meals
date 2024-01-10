describe('Test Dashboard View', () => {
    beforeEach(() => {
        cy.resetDB();
        cy.loginAs('kochomi');

    });

    it('should be able to display a dashboard with two weeks', () => {
        cy.intercept('GET', '**/api/events', { fixture: 'events.json', statusCode: 200 }).as('getEvents');
        cy.intercept('GET', '**/api/dashboard', { fixture: 'getDashboard.json', statusCode: 200 }).as('getDashboard');
        cy.visitMeals();

        cy.wait(['@getEvents', '@getDashboard']);

        cy.get('h2')
            .contains('Aktuelle Woche')
            .parent()
            .find('p')
            .contains('Mo. 8.1. - Fr. 12.1.')
            .should('exist');

        cy.get('h2')
            .contains('Nächste Woche')
            .parent()
            .find('p')
            .contains('Mo. 15.1. - Fr. 19.1.')
            .should('exist');

        cy.get('span')
            .contains('Mittwoch')
            .eq(0)
            .parent()
            .parent()
            .find('span')
            .contains('Afterwork')
            .parent()
            .parent()
            .find('div')
            .eq(1)
            .find('div')
            .eq(1)
            .contains('span', '17')
            .should('exist');

        cy.get('span')
            .contains('Mittwoch')
            .eq(0)
            .parent()
            .parent()
            .find('span')
            .contains('Limbs oh la la la (Ofen gebacken) + Finger food mit einer schlammigen Süß-Sauer-Soße')
            .parent()
            .find('p')
            .contains('Beschreibung - Limbs oh la la la (Ofen gebacken) + Finger food mit einer schlammigen Süß-Sauer-Soße')
            .should('exist');

        cy.get('span')
            .contains('Mittwoch')
            .eq(0)
            .parent()
            .parent()
            .find('span')
            .contains('Limbs DE')
            .parent()
            .find('p')
            .contains('Beschreibung - Limbs DE')
            .should('exist');
    });

    it('should be able to display a dashboard with one week', () => {
        cy.intercept('GET', '**/api/events', { fixture: 'events.json', statusCode: 200 }).as('getEvents');
        cy.intercept('GET', '**/api/dashboard', { fixture: 'getDashboard.json', statusCode: 200 }).as('getDashboard');
        cy.viewport(720, 1080);
        cy.visitMeals();

        cy.wait(['@getEvents', '@getDashboard']);

        cy.get('h2')
            .contains('Aktuelle Woche')
            .parent()
            .find('p')
            .contains('Mo. 8.1. - Fr. 12.1.')
            .should('exist');

        cy.get('div')
            .contains('Aktuelle Woche')
            .should('exist');

        cy.get('div')
            .contains('Nächste Woche')
            .click();

        cy.get('h2')
            .contains('Nächste Woche')
            .parent()
            .find('p')
            .contains('Mo. 15.1. - Fr. 19.1.')
            .should('exist');
    });

    it('should book a meal and cancel it', () => {
        cy.visitMeals();

        cy.get('h2')
            .contains('Nächste Woche')
            .parent()
            .parent()
            .find('[data-cy="mealCheckbox"]')
            .eq(0)
            .then((ele) => {
                // if ele has children, the meal is booked
                if (ele.children().length > 0) {
                    // cancel meal
                    cy.get('h2')
                        .contains('Nächste Woche')
                        .parent()
                        .parent()
                        .find('[data-cy="mealCheckbox"]')
                        .eq(0)
                        .click();

                    cy.wait(1000);
                    cy.get('h2')
                        .contains('Nächste Woche')
                        .parent()
                        .parent()
                        .find('[data-cy="mealCheckbox"]')
                        .eq(0)
                        .children()
                        .should('have.length', 0)

                    cy.get('h2')
                        .contains('Nächste Woche')
                        .parent()
                        .parent()
                        .find('[data-cy="mealCheckbox"]')
                        .eq(0)
                        .click();

                    cy.wait(1000);
                    cy.get('h2')
                        .contains('Nächste Woche')
                        .parent()
                        .parent()
                        .find('[data-cy="mealCheckbox"]')
                        .eq(0)
                        .children()
                        .should('have.length', 1)
                } else {
                    // book meal
                    cy.get('h2')
                        .contains('Nächste Woche')
                        .parent()
                        .parent()
                        .find('[data-cy="mealCheckbox"]')
                        .eq(0)
                        .click();

                    cy.wait(1000);
                    cy.get('h2')
                        .contains('Nächste Woche')
                        .parent()
                        .parent()
                        .find('[data-cy="mealCheckbox"]')
                        .eq(0)
                        .children()
                        .should('have.length', 1)

                    cy.get('h2')
                        .contains('Nächste Woche')
                        .parent()
                        .parent()
                        .find('[data-cy="mealCheckbox"]')
                        .eq(0)
                        .click();

                    cy.wait(1000);
                    cy.get('h2')
                        .contains('Nächste Woche')
                        .parent()
                        .parent()
                        .find('[data-cy="mealCheckbox"]')
                        .eq(0)
                        .children()
                        .should('have.length', 0)
                }
            });
    });
});