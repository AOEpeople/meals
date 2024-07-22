describe('Test Menu Participations View', () => {
    beforeEach(() => {
        cy.resetDB();
        cy.loginAs('kochomi');
        cy.visitMeals();

        // intercept the request to the backend
        cy.intercept('GET', '**/api/dishes', { fixture: 'dishes.json', statusCode: 200 }).as('getDishes');
        cy.intercept('GET', '**/api/categories', { fixture: 'categories.json', statusCode: 200 }).as('getCategories');
        cy.intercept('GET', '**/api/weeks', { fixture: 'weeks.json', statusCode: 200 }).as('getWeeks');
        cy.intercept('GET', '**/api/meals/count', { fixture: 'dishesCount.json', statusCode: 200 }).as('getDishesCount');
        cy.intercept('GET', '**/api/participations/*/abstaining', { fixture: 'abstaining.json', statusCode: 200 }).as('getAbstaining');
        cy.intercept('GET', '**/api/participations/*', { fixture: 'participations.json', statusCode: 200 }).as('getParticipations');
        cy.intercept('PUT', '**/api/participation/*/*', { fixture: 'putParticipation.json', statusCode: 200 }).as('putParticipation');
        cy.intercept('DELETE', '**/api/participation/*/*', { fixture: 'deleteParticipation.json', statusCode: 200 }).as('deleteParticipation');
        cy.intercept('GET', '**/api/week/lockdates/*', { fixture: 'lockdates.json', statusCode: 200 }).as('getLockdates');
    });

    it('should be able to visit the menu participations page', () => {
        cy.get('span > a').contains('Mahlzeiten').click();

        cy.wait(['@getWeeks']);

        cy.get('h4').eq(1).contains('Woche').click();
        cy.wait(['@getDishesCount', '@getCategories', '@getDishes', '@getLockdates']);

        cy.get('span').contains('Teilnahmen').click();
        cy.wait(['@getParticipations', '@getAbstaining']);

        cy.get('h2').should('contain', 'Teilnahmen');

        // Test Add Search Bar
        cy.get('input').first().click().type('meals');
        cy.wait(500);
        cy.get('li').each(($el) => {
            expect($el.text()).to.match(/Meals, */);
        });
        cy.get('input').clear().first().type('bob');
        cy.get('li').should('have.length', 1);
        cy.get('li').should('contain', 'Meals, Bob');
        cy.get('input').first().clear();
        cy.get('li').should('have.length', 1);
        cy.get('input').first().type('John');
        cy.get('li').should('have.length', 1);
        cy.get('li').should('contain', 'Meals, John');
        cy.get('input').first().parent().find('svg').click();
        cy.get('li').should('have.length', 1);

        // Add participant
        cy.get('input').first().click().type('alice')
        cy.get('li').contains('Meals, Alice').click();
        cy.get('h2').contains('Teilnahmen').click();
        cy.get('table')
            .find('span')
            .contains('Meals, Alice')
            .click()
            .parent()
            .parent()
            .parent()
            .find('td')
            .eq(4)
            .click()
            .find('svg')
            .should('exist');

        // Remove Participation
        cy.get('table')
            .find('span')
            .contains('Meals, Finance')
            .click()
            .parent()
            .parent()
            .parent()
            .find('td')
            .eq(4)
            .click()
            .find('svg')
            .should('not.exist');

        // Find meal counts
        const mealCounts = [/Gesamt/, /0/, /1/, /1/, /1/, /1/, /0/, /0/, /1/, /0/, /1/];
        cy.get('table')
            .find('span')
            .contains('Gesamt')
            .click()
            .parent()
            .parent()
            .find('td')
            .each(($el, index) => {
                expect($el.text()).to.match(mealCounts[index]);
            });

        // Tests search functionality
        cy.get('input[placeholder="Teilnehmer filtern"]').type('alice');

        cy.get('table')
            .find('span')
            .contains('Meals, Alice')
            .should('exist');

        cy.get('table')
            .find('span')
            .contains('Meals, Finance')
            .should('not.exist');

        cy.get('table')
            .find('span')
            .contains('Meals, Admin')
            .should('not.exist');

        cy.get('input[placeholder="Teilnehmer filtern"]').clear();

        cy.get('table')
            .find('span')
            .contains('Meals, Alice')
            .should('exist');

        cy.get('table')
            .find('span')
            .contains('Meals, Finance')
            .should('exist');

        cy.get('table')
            .find('span')
            .contains('Meals, Admin')
            .should('exist');

        // Assert weekdays
        cy.get('table').find('th').eq(1).should('contain', 'Montag');
        cy.get('table').find('th').eq(2).should('contain', 'Dienstag');
        cy.get('table').find('th').eq(3).should('contain', 'Mittwoch');
        cy.get('table').find('th').eq(4).should('contain', 'Donnerstag');
        cy.get('table').find('th').eq(5).should('contain', 'Freitag');
    });
});