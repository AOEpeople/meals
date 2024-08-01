describe('Test Dishes View', () => {
    beforeEach(() => {
        cy.resetDB();
        cy.loginAs('kochomi');
        cy.visitMeals();

        cy.intercept('GET', '**/api/dishes').as('getDishes');
        cy.intercept('POST', '**/api/dishes').as('postDishes');
        cy.intercept('PUT', '**/api/dishes/**').as('putDishes');
        cy.intercept('DELETE', '**/api/dishes/**').as('deleteDishes');
        cy.intercept('GET', '**/api/categories').as('getCategories');
        cy.intercept('POST', '**/api/dishes/**/variation').as('postVariation');
        cy.intercept('PUT', '**/api/dishes/variation/**').as('putVariation');
        cy.intercept('DELETE', '**/api/dishes/variation/**').as('deleteVariation');
    });

    it("should be able to navigate to '/dishes' and have the header displayed", () => {
        cy.get('span > a').contains('Gerichte').click({ force: true });

        cy.get('h2').should(ele => {
            expect(ele.first()).to.contain('Liste der Gerichte');
        });

        cy.contains('button', '+ Gericht erstellen');
        cy.get('input[placeholder="Suche nach Titel"]').should('exist');
    });

    it('should be able to switch the locale to english and back to german', () => {
        cy.get('span > a').contains('Gerichte').click({ force: true });

        // Switch language to english
        cy.get('span').contains('English version').parent().click({ force: true });

        // Check wether text has switched to english
        cy.get('h2').should(ele => {
            expect(ele.first()).to.contain('List of Dishes');
        });
        cy.contains('button', '+ create Dish');
        cy.contains('th', 'Actions');
        cy.contains('th', 'Title');
        cy.contains('th', 'Category');
        cy.get('input[placeholder="Search for title"]').should('exist');

        // Switch language back to german
        cy.get('span').contains('Deutsche Version').parent().click({ force: true });

        // Check wether text has switched to german
        cy.get('h2').should(ele => {
            expect(ele.first()).to.contain('Liste der Gerichte');
        });
        cy.contains('button', '+ Gericht erstellen');
        cy.contains('th', 'Aktionen');
        cy.contains('th', 'Titel');
        cy.contains('th', 'Kategorie');
        cy.get('input[placeholder="Suche nach Titel"]').should('exist');
    });

    it('should be able to create, edit and delete a dish', () => {
        cy.get('span > a').contains('Gerichte').click({ force: true });

        // Wait for the dishes and categories to load
        cy.wait(['@getDishes', '@getCategories']);

        // Create Dish
        cy.get('button').contains('+ Gericht erstellen').click();
        cy.wait(['@getCategories']);
        cy.get('h3').contains('Neues Gericht erstellen');
        cy.get('input[placeholder="Deutscher Titel"]').type('TestGericht1234')
            .invoke('val')
            .then(val => {
                expect(val).to.equal('TestGericht1234')
            });
        cy.get('input[placeholder="Englischer Titel"]').type('TestDish1234')
            .invoke('val')
            .then(val => {
                expect(val).to.equal('TestDish1234')
            });
        cy.contains('input', 'Speichern').click();
        cy.wait(['@getDishes']);
        cy.log('created dish');
        cy.get('[data-cy="msgClose"]').click();

        // Verify that the dish was created
        cy.get('button').contains('+ Gericht erstellen').click({ force: true });
        cy.get('span').contains('TestGericht1234');

        // Filter for the dish
        cy.get('input[placeholder="Suche nach Titel"]').type('TestGericht');
        cy.wait(1100);

        // Verify no VeggiIcon is Present
        cy.get('[data-cy="vegetarian-icon"]')
            .should('not.exist');
        cy.get('[data-cy="vegan-icon"]')
            .should('not.exist');

        // Edit Dish
        cy.get('span')
            .contains('TestGericht1234')
            .parent()
            .parent()
            .contains('Editieren')
            .click();
        cy.wait(['@getCategories']);
        cy.get('h3').contains('Gericht editieren');
        cy.get('input[placeholder="Deutscher Titel"]')
            .should('have.value', 'TestGericht1234')
            .clear({ force: true })
            .type('TestGericht5678', { force: true });
        cy.get('input[placeholder="Englischer Titel"]')
            .should('have.value', 'TestDish1234')
            .clear({ force: true })
            .type('TestDish5678', { force: true });
        cy.get('[data-cy="veggi-options"]')
            .find('button')
            .click({ force: true });
        cy.get('[data-cy="dropdown-options"]')
            .find('li')
            .eq(1)
            .click({ force: true });
        cy.contains('input', 'Speichern').click({ force: true });
        cy.wait(['@putDishes']);
        cy.log('edit dish');
        cy.wait(100);
        cy.get('[data-cy="msgClose"]').click();

        // Verify that the dish was edited
        cy.get('span').contains('TestGericht5678');
        cy.get('img[data-cy="vegan-icon"]')
            .should('exist');

        // Delete Dish
        cy.get('span')
            .contains('TestGericht5678')
            .parent()
            .parent()
            .contains('Löschen')
            .click();
        cy.log('delete dish');
        cy.wait(['@deleteDishes']);

        // Verify that the dish was deleted
        cy.get('[data-cy="msgClose"]').click();
        cy.get('span').contains('TestGericht5678').should('not.exist');
    });

    it('should be able to filter for a category', () => {
        cy.get('span > a').contains('Gerichte').click({ force: true });

        // Wait for the dishes and categories to load
        cy.wait(['@getDishes', '@getCategories']);

        // Filter for a category
        cy.get('input[placeholder="Suche nach Titel"]').type('Suppe');

        // Verify that the dishes were filtered
        cy.contains('td', 'Sonstiges').should('not.exist');
        cy.contains('td', 'Dessert').should('not.exist');
        cy.contains('td', 'Pasta').should('not.exist');

        // Filter for a category
        cy.get('input[placeholder="Suche nach Titel"]').clear().type('Dessert');

        // Verify that the dishes were filtered
        cy.contains('td', 'Sonstiges').should('not.exist');
        cy.contains('td', 'Suppe').should('not.exist');
        cy.contains('td', 'Pasta').should('not.exist');

        // Filter for a category
        cy.get('input[placeholder="Suche nach Titel"]').clear().type('Pasta');

        // Verify that the dishes were filtered
        cy.contains('td', 'Sonstiges').should('not.exist');
        cy.contains('td', 'Suppe').should('not.exist');
        cy.contains('td', 'Dessert').should('not.exist');
    });

    it('should be able to filter for a dish', () => {
        cy.get('span > a').contains('Gerichte').click({ force: true });

        // Wait for the dishes and categories to load
        cy.wait(['@getDishes', '@getCategories']);

        // Create a dish to filter for
        cy.get('button').contains('+ Gericht erstellen').click();
        cy.wait(['@getCategories']);
        cy.get('input[placeholder="Deutscher Titel"]').type('TestGericht1234', { force: true });
        cy.get('input[placeholder="Englischer Titel"]').type('TestDish1234', { force: true });
        cy.contains('input', 'Speichern').click({ force: true });
        cy.log('created dish to filter for');
        cy.wait(['@getDishes', '@postDishes'])

        // Filter for a dish
        cy.get('input[placeholder="Suche nach Titel"]').type('TestGericht1234');
        cy.wait(1100);
        cy.log('filtering for dish');

        // Verify that the dishes were filtered
        cy.get('span').contains('TestGericht1234').should('exist');

        // Verfify that only one dish is shown (title-row + dish-row)
        cy.get('span')
            .contains('TestGericht1234')
            .parent()
            .parent()
            .parent()
            .children()
            .should('have.length', 1);
        cy.log('verifiying that only one dish is shown');

        // Delete the dish
        cy.get('span')
            .contains('TestGericht1234')
            .parent()
            .parent()
            .contains('Löschen')
            .click();
    });

    it('should be able to create, edit and delete a dish variation', () => {
        cy.get('span > a').contains('Gerichte').click({ force: true });

        // Wait for the dishes and categories to load
        cy.wait(['@getDishes', '@getCategories']);

        // Create Dish
        cy.get('button').contains('+ Gericht erstellen').click();
        cy.wait(['@getCategories']);
        cy.get('h3').contains('Neues Gericht erstellen');
        cy.get('input[placeholder="Deutscher Titel"]').type('TestGericht1234', { force: true })
            .invoke('val')
            .then(val => {
                expect(val).to.equal('TestGericht1234')
            });
        cy.get('input[placeholder="Englischer Titel"]').type('TestDish1234', { force: true })
            .invoke('val')
            .then(val => {
                expect(val).to.equal('TestDish1234')
            });
        cy.contains('input', 'Speichern').click({ force: true });
        cy.wait(['@getDishes']);
        cy.log('created dish');
        cy.get('[data-cy="msgClose"]').click();

        // Filter for the dish
        cy.get('input[placeholder="Suche nach Titel"]').type('TestGericht');
        cy.wait(1100);

        // Verify no VeggiIcon is Present
        cy.get('[data-cy="vegetarian-icon"]')
            .should('not.exist');
        cy.get('[data-cy="vegan-icon"]')
            .should('not.exist');

        // Create a dish variation
        cy.get('span')
            .contains('TestGericht1234')
            .parent()
            .parent()
            .contains('Variation erstellen')
            .click();
        cy.get('h3').contains('Variation erstellen');
        cy.get('input[placeholder="Deutscher Titel"]').type('TestVariation1234', { force: true })
            .invoke('val')
            .then(val => {
                expect(val).to.equal('TestVariation1234')
            });
        cy.get('input[placeholder="Englischer Titel"]').type('TestVariation1234', { force: true })
            .invoke('val')
            .then(val => {
                expect(val).to.equal('TestVariation1234')
            });
        cy.get('[data-cy="veggi-options"]')
            .find('button')
            .click({ force: true });
        cy.get('[data-cy="dropdown-options"]')
            .find('li')
            .eq(2)
            .click({ force: true });
        cy.contains('input', 'Speichern').click({ force: true });
        cy.wait(['@getDishes']);
        cy.log('create variation');
        cy.get('[data-cy="msgClose"]').click();

        // Verify that the dish variation was created
        cy.get('span').contains('TestVariation1234').should('exist');
        cy.get('img[data-cy="vegetarian-icon"]')
            .should('exist');

        // Edit the dish variation
        cy.get('span')
            .contains('TestVariation1234')
            .parent()
            .parent()
            .contains('Editieren')
            .click();
        cy.get('h3').contains('Variation editieren');
        cy.get('input[placeholder="Deutscher Titel"]')
            .clear({ force: true })
            .type('TestVariation5678', { force: true });
        cy.get('input[placeholder="Englischer Titel"]')
            .clear({ force: true })
            .type('TestVariation5678', { force: true });
        cy.contains('input', 'Speichern').click({ force: true });
        cy.wait(['@putVariation']);
        cy.log('edit variation');
        cy.get('[data-cy="msgClose"]').click();

        // Verify that the dish variation was edited
        cy.get('span').contains('TestVariation5678').should('exist');
        cy.get('span').contains('TestVariation1234').should('not.exist');

        // Delete the dish variation and parent dish
        cy.get('span')
            .contains('TestVariation5678')
            .parent()
            .parent()
            .contains('Löschen')
            .click();
        cy.wait(['@deleteVariation', '@getDishes']);
        cy.log('delete variation');
        cy.get('[data-cy="msgClose"]').click();
        cy.get('span')
            .contains('TestGericht1234')
            .parent()
            .parent()
            .contains('Löschen')
            .click();
        cy.wait(['@getDishes', '@deleteDishes']);
        cy.log('delete dish');
        cy.get('[data-cy="msgClose"]').click();

        // Verify that the dish variation was deleted
        cy.get('span').contains('TestVariation5678').should('not.exist');
        cy.get('span').contains('TestGericht1234').should('not.exist');
    });
});