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

        cy.contains('input', 'Speichern').click();

        cy.wait('@putMenu').its('request.body').should(obj => {
            expect(JSON.stringify(obj)).to.equal(
                '{"id":58,"days":[{"meals":{"13":[{"dishSlug":"lamb-in-beersauce-with-potatodumblings","mealId":null,"participationLimit":0}],"14":[{"dishSlug":"tasty-worms","mealId":808,"participationLimit":0}]},"id":286,"enabled":true,"date":{"date":"2023-07-10 12:00:00.000000","timezone_type":3,"timezone":"Europe/Berlin"},"lockDate":{"date":"2023-07-08 16:00:00.000000","timezone_type":3,"timezone":"Europe/Berlin"}},{"meals":{"16":[{"dishSlug":"fish-so-juicy-sweat","mealId":809,"participationLimit":0}],"17":[{"dishSlug":"limbs","mealId":810,"participationLimit":0}]},"id":287,"enabled":true,"date":{"date":"2023-07-11 12:00:00.000000","timezone_type":3,"timezone":"Europe/Berlin"},"lockDate":{"date":"2023-07-10 16:00:00.000000","timezone_type":3,"timezone":"Europe/Berlin"}},{"meals":{"15":[{"dishSlug":"innards-v1","mealId":813,"participationLimit":0},{"dishSlug":"innards-v2","mealId":812,"participationLimit":0}],"30":[{"dishSlug":"pork-in-beersauce-with-potatodumblings","mealId":814,"participationLimit":0}]},"id":288,"enabled":true,"date":{"date":"2023-07-12 12:00:00.000000","timezone_type":3,"timezone":"Europe/Berlin"},"lockDate":{"date":"2023-07-11 16:00:00.000000","timezone_type":3,"timezone":"Europe/Berlin"}},{"meals":{"15":[{"dishSlug":"innards","mealId":815,"participationLimit":0}],"18":[{"dishSlug":"century-eggs-paired-with-a-compote-of-seasonal-berries-and-rye-bread-v1","mealId":816,"participationLimit":0}]},"id":289,"enabled":true,"date":{"date":"2023-07-13 12:00:00.000000","timezone_type":3,"timezone":"Europe/Berlin"},"lockDate":{"date":"2023-07-12 16:00:00.000000","timezone_type":3,"timezone":"Europe/Berlin"}},{"meals":{"19":[{"dishSlug":"limbs-oh-la-la-la-oven-backed-finger-food-with-a-slimy-sweet-and-sour-sauce","mealId":817,"participationLimit":0}],"-1":[]},"id":290,"enabled":true,"date":{"date":"2023-07-14 12:00:00.000000","timezone_type":3,"timezone":"Europe/Berlin"},"lockDate":{"date":"2023-07-13 16:00:00.000000","timezone_type":3,"timezone":"Europe/Berlin"}}],"notify":false,"enabled":true}'
            );
        });
    });
});