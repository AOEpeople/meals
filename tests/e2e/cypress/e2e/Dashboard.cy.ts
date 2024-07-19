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
            .find('div > div')
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

        cy.log('Starting process');
        cy.get('h2')
            .contains('Nächste Woche')
            .parent()
            .parent()
            .find('[data-cy="mealCheckbox"]')
            .eq(0)
            .then((ele) => {
                cy.log('Then');
                // if ele has children, the meal is booked
                if (ele.children().length > 0) {
                    cy.log('length > 0');
                    // cancel meal
                    cy.get('h2')
                        .contains('Nächste Woche')
                        .parent()
                        .parent()
                        .find('[data-cy="mealCheckbox"]')
                        .eq(0)
                        .click();

                    cy.log('clicked cancel');
                    cy.wait(1000);
                    cy.get('h2')
                        .contains('Nächste Woche')
                        .parent()
                        .parent()
                        .find('[data-cy="mealCheckbox"]')
                        .eq(0)
                        .children()
                        .should('have.length', 0)

                    cy.wait(600);
                    cy.log('has length 0 and waited 600ms');
                    cy.get('h2')
                        .contains('Nächste Woche')
                        .parent()
                        .parent()
                        .find('[data-cy="mealCheckbox"]')
                        .eq(0)
                        .click();

                    cy.wait(1000);
                    cy.log('clicked to join again');
                    cy.get('h2')
                        .contains('Nächste Woche')
                        .parent()
                        .parent()
                        .find('[data-cy="mealCheckbox"]')
                        .eq(0)
                        .children()
                        .should('have.length', 1);
                    cy.log('joined and verified');
                } else {
                    cy.log('length < 0');
                    // book meal
                    cy.get('h2')
                        .contains('Nächste Woche')
                        .parent()
                        .parent()
                        .find('[data-cy="mealCheckbox"]')
                        .eq(0)
                        .click();

                    cy.wait(1000);
                    cy.log('clicked to join initially');
                    cy.get('h2')
                        .contains('Nächste Woche')
                        .parent()
                        .parent()
                        .find('[data-cy="mealCheckbox"]')
                        .eq(0)
                        .children()
                        .should('have.length', 1)

                    cy.wait(600);
                    cy.log('verified join and waited 600');
                    cy.get('h2')
                        .contains('Nächste Woche')
                        .parent()
                        .parent()
                        .find('[data-cy="mealCheckbox"]')
                        .eq(0)
                        .click();

                    cy.wait(1000);
                    cy.log('clicked to leave again');
                    cy.get('h2')
                        .contains('Nächste Woche')
                        .parent()
                        .parent()
                        .find('[data-cy="mealCheckbox"]')
                        .eq(0)
                        .children()
                        .should('have.length', 0);
                    cy.log('verified left');
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
        cy.log('found event')

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
            .find('div > div')
            .eq(6)
            .children()
            .should('have.length', 0);
        cy.log('event has not been joined yet');

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
            .find('div > div')
            .eq(6)
            .click();
        cy.log('joined afterwork');

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
            .find('div > div')
            .eq(6)
            .children()
            .should('have.length', 1);
        cy.log('verified joined afterwork');

        // leave afterwork after waiting for btn to debounce
        cy.wait(600);
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
            .find('div > div')
            .eq(6)
            .click();
        cy.log('leaving afterwork');

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
            .find('div > div')
            .eq(6)
            .children()
            .should('have.length', 0);
        cy.log('verified left afterwork');
    });

    it('should join an event and be in the participants list', () => {
        cy.intercept('GET', '**/api/weeks').as('getWeeks');
        cy.intercept('GET', '**/api/meals/count').as('getDishesCount');
        cy.intercept('GET', '**/api/categories').as('getCategories');
        cy.intercept('GET', '**/api/dishes').as('getDishes');
        cy.intercept('GET', '**/api/events').as('getEvents');
        cy.intercept('GET', '**/api/dashboard').as('getDashboard');
        cy.intercept('GET', '**/api/participations/event/**').as('getParticipants');


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
        cy.log('found event afterwork');

        // go to dashboard
        cy.get('header > nav > div > a > svg').click();
        cy.wait(['@getDashboard', '@getEvents']);

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
            .find('div > div')
            .eq(6)
            .click();
        cy.log('joined afterwork');

        // click on the info-icon
        cy.get('h2')
            .contains('Nächste Woche')
            .parent()
            .parent()
            .contains('Montag')
            .parent()
            .parent()
            .find('span')
            .contains('Afterwork')
            .parent()
            .find('svg')
            .eq(1)
            .click();

        cy.wait('@getParticipants');

        // verify participants on popup
        cy.get('span')
            .contains('Teilnahmen "Afterwork"')
            .parent()
            .parent()
            .find('li')
            .contains('Meals, Kochomi')
            .parent()
            .parent()
            .find('span')
            .contains('Es gibt 1 Teilnehmer')
            .parent()
            .parent()
            .find('svg')
            .click();
        cy.log('verified joined afterwork');

        // leave afterwork after waiting for btn to debounce
        cy.wait(600);
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
            .find('div > div')
            .eq(6)
            .click();
        cy.log('leaving afterwork')

        // click on the info-icon
        cy.get('h2')
            .contains('Nächste Woche')
            .parent()
            .parent()
            .contains('Montag')
            .parent()
            .parent()
            .find('span')
            .contains('Afterwork')
            .parent()
            .find('svg')
            .eq(1)
            .click();

        cy.wait('@getParticipants');

        // verify no participants on popup
        cy.get('span')
            .contains('Teilnahmen "Afterwork"')
            .parent()
            .parent()
            .find('span')
            .contains('Noch keine Teilnehmer für dieses Event')
            .parent()
            .parent()
            .find('span')
            .contains('Es gibt 0 Teilnehmer')
            .parent()
            .parent()
            .find('svg')
            .click()
        cy.log('verified left afterwork');
    });

    it('should show the veggi icons on vegetarian/vegan meals', () => {
        cy.intercept('GET', '**/api/events', { fixture: 'events.json', statusCode: 200 }).as('getEvents');
        cy.intercept('GET', '**/api/dashboard', { fixture: 'getDashboard.json', statusCode: 200 }).as('getDashboard');

        cy.visitMeals();
        cy.wait(['@getDashboard', '@getEvents']);

        cy.get('span')
            .contains('Montag')
            .eq(0)
            .parent()
            .parent()
            .find('span')
            .contains('Braaaaaiiinnnzzzzzz DE')
            .parent()
            .find('img[data-cy="vegan-icon"]')
            .should('exist');

        cy.get('span')
            .contains('Montag')
            .eq(0)
            .parent()
            .parent()
            .find('p')
            .contains('Innards DE #v1')
            .parent()
            .find('img[data-cy="vegan-icon"]')
            .should('exist');

        cy.get('span')
            .contains('Montag')
            .eq(0)
            .parent()
            .parent()
            .find('p')
            .contains('Innards DE #v2')
            .parent()
            .find('img[data-cy="vegetarian-icon"]')
            .should('exist');

        cy.get('span')
            .contains('Dienstag')
            .eq(0)
            .parent()
            .parent()
            .find('span')
            .contains('Fish (so juicy sweat) DE')
            .parent()
            .find('img[data-cy="vegetarian-icon"]')
            .should('exist');

        cy.get('span')
            .contains('Dienstag')
            .eq(0)
            .parent()
            .parent()
            .find('span')
            .contains('Tasty Worms DE')
            .parent()
            .find('img[data-cy="vegetarian-icon"]')
            .should('not.exist');

        cy.get('span')
            .contains('Dienstag')
            .eq(0)
            .parent()
            .parent()
            .find('span')
            .contains('Tasty Worms DE')
            .parent()
            .find('img[data-cy="vegan-icon"]')
            .should('not.exist');
    });
});