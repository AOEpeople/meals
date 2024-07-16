describe('Test Creating a Menu', () => {
    beforeEach(() => {
        cy.resetDB();
        cy.loginAs('kochomi');
        cy.visitMeals();

        // spy on the request to the backend to wait for them to resolve before testing
        cy.intercept('GET', '**/api/weeks').as('getWeeks');
        cy.intercept('POST', '**/api/weeks/*').as('postWeeks');
        cy.intercept('GET', '**/api/meals/count').as('getDishesCount');
        cy.intercept('PUT', '**/api/menu/*').as('putMenu');
        cy.intercept('GET', '**/api/categories').as('getCategories');
        cy.intercept('GET', '**/api/dishes').as('getDishes');
        cy.intercept('GET', '**/api/participations/*/abstaining').as('getAbstaining');
        cy.intercept('GET', '**/api/participations/*').as('getParticipations');
        cy.intercept('PUT', '**/api/participation/*/*').as('putParticipation');
        cy.intercept('DELETE', '**/api/participation/*/*').as('deleteParticipation');
        cy.intercept('GET', '**/api/events').as('getEvents');
    });

    it('should create a week on submitting a valid menu', () => {
        cy.get('span > a').contains('Mahlzeiten').click();

        cy.wait(['@getWeeks']);

        // Go to 7th week (it should not have been created yet because of db reset)
        cy.get('h4').eq(6).contains('Woche').click();

        cy.wait(['@getDishesCount', '@getCategories', '@getDishes']);

        // create menu
        // Monday
        cy.get('input')
            .first()
            .parent()
            .find('svg')
            .eq(1)
            .click()
            .parent()
            .find('input')
            .click()
            .type('Tasty')
            .parent().parent()
            .find('li').contains('Tasty Worms DE')
            .click();

        cy.get('h2').should('contain', 'Woche').click();

        cy.get('input')
            .eq(1)
            .parent()
            .find('svg')
            .eq(1)
            .click()
            .parent()
            .find('input')
            .click()
            .parent().parent()
            .find('li').contains('Limbs DE')
            .click();

        cy.get('h2').should('contain', 'Woche').click();

        // Tuesday
        cy.get('input')
            .eq(3)
            .parent()
            .find('input')
            .click()
            .parent().parent()
            .find('li').contains('Braaaaaiiinnnzzzzzz DE')
            .click();

        cy.get('h2').should('contain', 'Woche').click();

        cy.get('input')
            .eq(4)
            .parent()
            .find('input')
            .click()
            .parent().parent()
            .find('li').contains('Fish (so juicy sweat) DE')
            .click();

        cy.get('h2').should('contain', 'Woche').click();

        // Wednesday
        cy.get('input')
            .eq(6)
            .parent()
            .find('input')
            .click()
            .parent().parent()
            .find('li').contains('Innards DE')
            .click();

        cy.get('h2').should('contain', 'Woche').click();

        cy.get('input')
            .eq(7)
            .parent()
            .find('input')
            .click()
            .parent().parent()
            .find('li')
            .contains('Limbs oh la la la (Ofen gebacken) + Finger food mit einer schlammigen Süß-Sauer-Soße')
            .click();

        cy.get('h2').should('contain', 'Woche').click();

        cy.get('input')
            .eq(8)
            .parent()
            .find('input')
            .click()
            .parent().parent()
            .find('li')
            .contains('Alumni Afterwork')
            .click();

        cy.get('h2').should('contain', 'Woche').click();

        // Thursday
        cy.get('input')
            .eq(9)
            .parent()
            .find('input')
            .click()
            .parent().parent()
            .find('li').contains('Innards DE')
            .click()
            .parent()
            .find('button').contains('Variation')
            .click()
            .parent()
            .find('li > div > span').contains('Innards DE #v1')
            .click();

        cy.get('h2').should('contain', 'Woche').click().click();

        cy.get('input')
            .eq(10)
            .parent()
            .find('input')
            .click()
            .parent().parent()
            .find('li')
            .contains('Limbs DE')
            .click();

        cy.get('h2').should('contain', 'Woche').click();

        // Friday
        cy.get('input')
            .eq(12)
            .parent()
            .find('input')
            .click()
            .parent().parent()
            .find('li').contains('Fish (so juicy sweat) DE')
            .click();

        cy.get('h2').should('contain', 'Woche').click();

        // Save
        cy.contains('input', 'Speichern').click();

        cy.wait(['@postWeeks', '@getWeeks']);

        cy.get('[data-cy="msgClose"]').click();

        // Find the saved event
        cy.get('input')
            .eq(8)
            .should('have.value', 'Alumni Afterwork');

        // Edit Menu
        cy.get('input')
            .eq(12)
            .parent()
            .find('input')
            .click()
            .parent().parent()
            .find('li').contains('Innards DE')
            .click();

        cy.get('h2').should('contain', 'Woche').click();

        cy.get('input')
            .eq(8)
            .parent()
            .find('svg')
            .eq(1)
            .click()

        cy.get('h2').should('contain', 'Woche').click();

        cy.get('input')
            .eq(13)
            .parent()
            .find('input')
            .click()
            .parent().parent()
            .find('li').contains('Braaaaaiiinnnzzzzzz DE')
            .click();

        cy.get('h2').should('contain', 'Woche').click();

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

        // Save
        cy.contains('input', 'Speichern').click();

        cy.wait(['@putMenu', '@getWeeks']);

        // Check that all meals and the event are saved
        cy.get('input')
            .eq(0)
            .should('have.value', 'Tasty Worms DE');

        cy.get('input')
            .eq(1)
            .should('have.value', 'Limbs DE');

        cy.get('input')
            .eq(3)
            .should('have.value', 'Braaaaaiiinnnzzzzzz DE');

        cy.get('input')
            .eq(4)
            .should('have.value', 'Fish (so juicy sweat) DE');

        cy.get('input')
            .eq(6)
            .should('have.value', 'Innards DE');

        cy.get('input')
            .eq(7)
            .should('have.value', 'Limbs oh la la la (Ofen gebacken) + Finger food mit einer schlammigen Süß-Sauer-Soße');

        cy.get('input')
            .eq(8)
            .should('have.value', '');

        cy.get('input')
            .eq(9)
            .should('have.value', 'Innards DE, Innards DE #v1');

        cy.get('input')
            .eq(10)
            .should('have.value', 'Limbs DE');

        cy.get('input')
            .eq(12)
            .should('have.value', 'Braaaaaiiinnnzzzzzz DE');

        cy.get('input')
            .eq(13)
            .should('have.value', 'Innards DE');

        // Test Participations
        cy.get('span').contains('Teilnahmen').click();
        cy.wait(['@getParticipations', '@getAbstaining']);

        cy.get('th').contains('Tasty Worms DE');
        cy.get('th').contains('Limbs DE');
        cy.get('th').contains('Braaaaaiiinnnzzzzzz DE');
        cy.get('th').contains('Fish (so juicy sweat) DE');
        cy.get('th').contains('Innards DE');
        cy.get('th').contains('Limbs oh la la la (Ofen gebacken) + Finger food mit einer schlammigen Süß-Sauer-Soße');
        cy.get('th').contains('Innards DE #v1');

        // Add participant
        cy.get('input').first().click().type('alice');
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
            .click();

        cy.wait('@putParticipation');

        cy.get('table')
            .find('span')
            .contains('Meals, Alice')
            .click()
            .parent()
            .parent()
            .parent()
            .find('td')
            .eq(4)
            .find('svg')
            .should('exist');

        // Add participant
        cy.get('input').first().click().clear().type('finance');
        cy.get('li').contains('Meals, Finance').click();
        cy.get('table')
            .find('span')
            .contains('Meals, Finance')
            .click()
            .parent()
            .parent()
            .parent()
            .find('td')
            .eq(4)
            .click();

        cy.wait('@putParticipation');

        cy.get('table')
            .find('span')
            .contains('Meals, Finance')
            .click()
            .parent()
            .parent()
            .parent()
            .find('td')
            .eq(4)
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
            .click();

        cy.wait('@deleteParticipation');

        cy.get('table')
            .find('span')
            .contains('Meals, Finance')
            .click()
            .parent()
            .parent()
            .parent()
            .find('td')
            .eq(4)
            .find('svg')
            .should('not.exist');
    });

    it('should create a menu with participation limits, delete a meal in the first row of a day and be able to set it again', () => {
        cy.get('span > a').contains('Mahlzeiten').click();

        cy.wait(['@getWeeks']);

        // Go to 7th week (it should not have been created yet because of db reset)
        cy.get('h4').eq(6).contains('Woche').click();

        cy.wait(['@getDishesCount', '@getCategories', '@getDishes']);

        // create menu
        // Monday
        cy.get('input')
            .first()
            .parent()
            .find('svg')
            .eq(1)
            .click()
            .parent()
            .find('input')
            .click()
            .type('Tasty')
            .parent().parent()
            .find('li').contains('Tasty Worms DE')
            .click();

        cy.get('h2').should('contain', 'Woche').click();

        cy.get('input')
            .eq(1)
            .parent()
            .find('svg')
            .eq(1)
            .click()
            .parent()
            .find('input')
            .click()
            .parent().parent()
            .find('li').contains('Limbs DE')
            .click();

        cy.get('h2').should('contain', 'Woche').click();

        // Save
        cy.contains('input', 'Speichern').click();

        cy.wait(['@postWeeks', '@getWeeks']);

        cy.get('[data-cy="msgClose"]').click();

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
            .click();

        cy.get('[data-cy="meal-participation-limit-input"]')
            .eq(1)
            .clear()
            .type('17');

        cy.get('span').contains('Limit').parent().find('svg').click();

        // Delete Meal
        cy.get('h2').should('contain', 'Woche').click();

        cy.get('input')
            .first()
            .parent()
            .find('svg')
            .eq(1)
            .click();

        cy.get('h2').should('contain', 'Woche').click();

        // Save
        cy.contains('input', 'Speichern').click();

        cy.wait(['@putMenu', '@getWeeks']);

        cy.get('[data-cy="msgClose"]').click();

        // Check Limbs is now set as first input
        cy.get('input')
            .first()
            .should('have.value', 'Limbs DE');

        // Set new second Meal
        cy.get('input')
            .eq(1)
            .parent()
            .find('svg')
            .eq(1)
            .click()
            .parent()
            .find('input')
            .click()
            .parent().parent()
            .find('li').contains('Braaaaaiiinnnzzzzzz DE')
            .click();

        cy.get('h2').should('contain', 'Woche').click();

        // Save
        cy.contains('input', 'Speichern').click();

        cy.wait(['@putMenu', '@getWeeks']);

        cy.get('[data-cy="msgClose"]').click();

        // Check Meals are both saved
        cy.get('input')
            .first()
            .should('have.value', 'Braaaaaiiinnnzzzzzz DE');

        cy.get('input')
            .eq(1)
            .should('have.value', 'Limbs DE');

        // Check participation limits
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
            .click();

        cy.get('[data-cy="meal-participation-limit-input"]')
            .eq(0)
            .should('have.value', 0);

        cy.get('[data-cy="meal-participation-limit-input"]')
            .eq(1)
            .should('have.value', 17);
    });

    it('should not create a menu if the initial submission of a menu gets aborted', () => {
        cy.get('span > a').contains('Mahlzeiten').click();

        // Go to 7th week (it should not have been created yet because of db reset)
        cy.get('h4').eq(6).contains('Woche').click();

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
            .type('Tasty')
            .parent().parent()
            .find('li').contains('Tasty Worms DE')
            .click();

        cy.get('h2').should('contain', 'Woche').click();

        cy.contains('div', 'Abbrechen').click();

        cy.get('h4')
            .eq(6)
            .contains('Woche')
            .parent()
            .find('div')
            .should('not.exist');
    });
});