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

    it('should create an event, book it then leave the event again', () => {
        cy.intercept('GET', '**/api/weeks').as('getWeeks');
        cy.intercept('GET', '**/api/meals/count').as('getDishesCount');
        cy.intercept('GET', '**/api/categories').as('getCategories');
        cy.intercept('GET', '**/api/dishes').as('getDishes');
        cy.intercept('GET', '**/api/events').as('getEvents');
        cy.intercept('GET', '**/api/dashboard').as('getDashboard');


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
            .parent()
            .find('div')
            .eq(4)
            .children()
            .should('have.length', 0);

        // join afterwork
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
            .parent()
            .find('div')
            .eq(4)
            .click();

        // confirm event has been joined
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
            .parent()
            .find('div')
            .eq(4)
            .children()
            .should('have.length', 1);

        // leave afterwork
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
            .parent()
            .find('div')
            .eq(4)
            .click();

        // confirm event has been left
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
            .parent()
            .find('div')
            .eq(4)
            .children()
            .should('have.length', 0);
    });
});