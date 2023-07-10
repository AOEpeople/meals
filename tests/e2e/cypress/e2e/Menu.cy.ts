describe('Test Weeks View', () => {
    beforeEach(() => {
        cy.setCookie('locale', 'de');
        cy.loginAs('kochomi');
        cy.visitMeals();

        // intercept the request to the backend
        cy.intercept('GET', '**/api/weeks', { fixture: 'weeks.json', statusCode: 200 }).as('getWeeks');
        cy.intercept('POST', '**/api/weeks/*', { fixture: 'Success.json', statusCode: 200 }).as('postWeeks');
        cy.intercept('GET', '**/api/meals/count', { fixture: 'dishesCount.json', statusCode: 200 }).as('getDishesCount');
        cy.intercept('PUT', '**/api/menu/*', { fixture: 'Success.json', statusCode: 200 }).as('putMenu');
        cy.intercept('GET', '**/api/categories', { fixture: 'categories.json', statusCode: 200 }).as('getCategories');
        cy.intercept('GET', '**/api/dishes', { fixture: 'dishes.json', statusCode: 200 }).as('getDishes');
    });

    it('should be able to browse to the menu page from the weekspage', () => {

        cy.visit('/weeks');

        cy.wait(['@getWeeks']);

        // Hide Symphony's toolbar
        cy.get('a[class="hide-button"]').click();

        cy.get('h4').contains('Woche #28').click();

        cy.wait(['@getDishesCount', '@getCategories', '@getDishes']);

        cy.url().should('include', '/menu');
        cy.get('h2').should('contain', 'Editiere Woche #28 (10.07. - 14.07.)');

        // change input
        cy.get('input')
            .first()
            .parent()
            .find('svg')
            .click()
            .parent()
            .find('input')
            .click()
            .type('Lammhaxxe')
            .parent().parent()
            .find('li').contains('Lammhaxxe in Biersoße mit Klößen')
            .click();
        cy.get('h2').should('contain', 'Editiere Woche #28 (10.07. - 14.07.)').click();

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
        cy.get('[data-cy="meal-lockdate-input"]')
            .clear()
            .type('2023-07-08T12:00')
        cy.get('span').contains('Sperren').parent().find('svg').click();

        // change input
        cy.get('input')
            .eq(1)
            .parent()
            .find('svg')
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
        cy.get('h2').should('contain', 'Editiere Woche #28 (10.07. - 14.07.)').click();

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