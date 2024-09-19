describe('Test Weeks View', () => {
    beforeEach(() => {
        cy.resetDB();
        cy.loginAs('kochomi');
        cy.visitMeals();

        // intercept the request to the backend
        cy.intercept('GET', '**/api/weeks', { fixture: 'weeks.json', statusCode: 200 }).as('getWeeks');
        cy.intercept('POST', '**/api/weeks/*', { fixture: 'Success.json', statusCode: 200 }).as('postWeeks');
        cy.intercept('GET', '**/api/meals/count', { fixture: 'dishesCount.json', statusCode: 200 }).as('getDishesCount');
        cy.intercept('PUT', '**/api/menu/*', { fixture: 'Success.json', statusCode: 200 }).as('putMenu');
        cy.intercept('GET', '**/api/categories', { fixture: 'categories.json', statusCode: 200 }).as('getCategories');
        cy.intercept('GET', '**/api/dishes', { fixture: 'dishes.json', statusCode: 200 }).as('getDishes');
        cy.intercept('GET', '**/api/events', { fixture: 'events.json', statusCode: 200 }).as('getEvents');
        cy.intercept('GET', '**/api/week/lockdates/*', { fixture: 'lockdates.json', statusCode: 200 }).as('getLockdates');
    });

    it('should be able to browse to the menu page from the weekspage', () => {
        cy.get('span > a').contains('Mahlzeiten').click();

        cy.wait(['@getWeeks']);

        cy.get('h4').contains('Woche #28').click();

        cy.wait(['@getDishesCount', '@getCategories', '@getDishes', '@getEvents', '@getLockdates']);

        cy.url().should('include', '/menu');
        cy.get('h2').invoke('text').should('match', /Woche bearbeiten #\d{1,2} \(\d{1,2}.\d{1,2}. - \d{1,2}.\d{1,2}.\)/);

        // change input
        cy.get('input')
            .first()
            .parent()
            .find('svg')
            .eq(1)
            .click()
            .parent()
            .find('input')
            .click()
            .type('Lammhaxxe')
            .parent().parent()
            .find('li').contains('Lammhaxxe in Biersoße mit Klößen')
            .click();
        cy.get('h2').should('contain', 'Woche bearbeiten #28').click();

        // change event
        cy.get('input')
            .eq(11)
            .parent()
            .find('input')
            .click()
            .parent().parent()
            .find('li')
            .contains('Alumni Afterwork')
            .click();

        cy.get('h2').should('contain', 'Woche').click();

        // change week enabled
        cy.get('span')
            .contains('Diese Woche ist aktiv')
            .parent()
            .find('button')
            .click();

        // change notify
        cy.get('span')
            .contains('In Mattermost bekanntgeben')
            .parent()
            .find('button')
            .click();

        // change day enabled
        cy.get('span')
            .contains('Dieser Tag ist aktiviert')
            .parent()
            .click();

        // change participation limit
        cy.get('input')
            .first()
            .parent()
            .parent()
            .parent()
            .parent()
            .find('div.col-start-1')
            .first()
            .find('button')
            .first()
            .click()
        cy.get('[data-cy="meal-participation-limit-input"]')
            .first()
            .clear()
            .type('17');
        cy.get('span').contains('Limit').parent().find('svg').click();
        cy.get('h2').should('contain', 'Woche bearbeiten #28').click();

        // change lock date
        cy.get('input')
            .first()
            .parent()
            .parent()
            .parent()
            .parent()
            .find('div.col-start-1')
            .find('button')
            .last()
            .click()
        cy.get('[aria-label="Datepicker input"]')
            .click()
        cy.get('div[class^="dp__cell_inner"]')
            .contains(new RegExp(/^7$/))
            .click()
        cy.get('span').contains('Sperren').parent().find('svg').click();
        cy.get('h2').should('contain', 'Woche bearbeiten #28').click();

        // change input
        cy.get('input')
            .eq(1)
            .parent()
            .find('svg')
            .eq(1)
            .click()
            .parent()
            .find('input')
            .click()
            .type('Innards')
            .parent().parent()
            .find('li').contains('Innards DE')
            .click();
        cy.get('button').contains('Variation').click();
        cy.get('span').contains('Innards DE #v1').click();
        cy.get('h2').should('contain', 'Woche bearbeiten #28').click();

        cy.contains('input', 'Speichern').click();

        cy.fixture('menuPut.json').then(menuPut => {
            cy.wait('@putMenu').its('request.body').should(obj => {
                expect(JSON.stringify(obj)).to.equal(JSON.stringify(menuPut));
            });
        });

        cy.contains('div', 'Abbrechen').click();
        cy.url().should('include', '/weeks');
        cy.get('h2').should('contain', 'Liste der Wochen');
    });
});