describe('Test Dishes View', () => {
    beforeEach(() => {
        cy.resetDB();
        cy.loginAs('kochomi');
        cy.visitMeals();

        cy.intercept('GET', '**/api/dishes').as('getDishes');
        cy.intercept('GET', '**/api/categories').as('getCategories');
    });

    it("should be able to navigate to '/dishes' and have the header displayed", () => {
        cy.get('span > a').contains('Gerichte').click();

        cy.get('h2').should(ele => {
            expect(ele.first()).to.contain('Liste der Gerichte');
        });

        cy.contains('button', '+ Gericht erstellen');
        cy.get('input[placeholder="Suche nach Titel"]').should('exist');
    });

    it('should be able to switch the locale to english and back to german', () => {
        cy.get('span > a').contains('Gerichte').click();

        // Switch language to english
        cy.get('span').contains('English version').parent().click();

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
        cy.get('span').contains('Deutsche Version').parent().click();

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
        cy.get('h3').contains('Neues Gericht erstellen');
        cy.get('input[placeholder="Deutscher Titel"]').type('TestGericht1234');
        cy.get('input[placeholder="Englischer Titel"]').type('TestDish1234');
        cy.get('input[placeholder="Deutsche Beschreibung"]').type('TestBeschreibung1234');
        cy.get('input[placeholder="Englische Beschreibung"]').type('TestDescription1234');
        cy.get('span').contains('Sonstiges').click();
        cy.get('li').children().contains('Vegetarisch').click();
        cy.get('label').contains('Dieses Gericht ist nicht teilbar').click();
        cy.contains('input', 'Speichern').click();
        cy.get('[data-cy="msgClose"]').click();

        // Verify that the dish was created
        cy.get('button').contains('+ Gericht erstellen').click();
        cy.get('span').contains('TestGericht1234');

        // Filter for the dish
        cy.get('input[placeholder="Suche nach Titel"]').type('TestGericht');

        // Edit Dish
        cy.get('span')
            .contains('TestGericht1234')
            .parent()
            .parent()
            .contains('Editieren')
            .click();
        cy.get('h3').contains('Gericht editieren');
        cy.get('input[placeholder="Deutscher Titel"]')
            .should('have.value', 'TestGericht1234')
            .clear()
            .type('TestGericht5678');
        cy.get('input[placeholder="Englischer Titel"]')
            .should('have.value', 'TestDish1234')
            .clear()
            .type('TestDish5678');
        cy.get('input[placeholder="Deutsche Beschreibung"]')
            .should('have.value', 'TestBeschreibung1234')
            .clear()
            .type('TestBeschreibung5678');
        cy.get('input[placeholder="Englische Beschreibung"]')
            .should('have.value', 'TestDescription1234')
            .clear()
            .type('TestDescription5678');
        cy.get('span').contains('Vegetarisch').click();
        cy.get('li').children().contains('Fleisch').click();
        cy.get('label').contains('Dieses Gericht ist nicht teilbar').click();
        cy.contains('input', 'Speichern').click();
        cy.get('[data-cy="msgClose"]').click();

        // Verify that the dish was edited
        cy.get('span')
            .contains('TestGericht5678')
            .parent()
            .parent()
            .contains('Editieren')
            .click();
        cy.get('span').contains('TestGericht5678');

        // Delete Dish
        cy.get('span')
            .contains('TestGericht5678')
            .parent()
            .parent()
            .contains('Löschen')
            .click();
        cy.get('[data-cy="msgClose"]').click();

        // Verify that the dish was deleted
        cy.get('span').contains('TestGericht5678').should('not.exist');
    });

    it('should be able to filter for a category', () => {
        cy.get('span > a').contains('Gerichte').click();

        // Wait for the dishes and categories to load
        cy.wait(['@getDishes', '@getCategories']);

        // Filter for a category
        cy.get('input[placeholder="Suche nach Titel"]').type('Vegetarisch');

        // Verify that the dishes were filtered
        cy.get('td').contains('Sonstiges').should('not.exist');
        cy.get('td').contains('Fleisch').should('not.exist');

        // Filter for a category
        cy.get('input[placeholder="Suche nach Titel"]').clear().type('Fleisch');

        // Verify that the dishes were filtered
        cy.get('td').contains('Sonstiges').should('not.exist');
        cy.get('td').contains('Vegetarisch').should('not.exist');
    });

    it('should be able to filter for a dish', () => {
        cy.get('span > a').contains('Gerichte').click();

        // Wait for the dishes and categories to load
        cy.wait(['@getDishes', '@getCategories']);

        // Create a dish to filter for
        cy.get('button').contains('+ Gericht erstellen').click();
        cy.get('input[placeholder="Deutscher Titel"]').type('TestGericht1234');
        cy.get('input[placeholder="Englischer Titel"]').type('TestDish1234');
        cy.contains('input', 'Speichern').click();
        cy.get('button').contains('+ Gericht erstellen').click();
        cy.get('[data-cy="msgClose"]').click();

        // Filter for a dish
        cy.get('input[placeholder="Suche nach Titel"]').type('TestGericht1234');

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

        // Delete the dish
        cy.get('span')
            .contains('TestGericht1234')
            .parent()
            .parent()
            .contains('Löschen')
            .click();
        cy.get('[data-cy="msgClose"]').click();
    });

    it('should be able to create, edit and delete a dish variation', () => {
        cy.get('span > a').contains('Gerichte').click();

        // Wait for the dishes and categories to load
        cy.wait(['@getDishes', '@getCategories']);

        // Create a dish
        cy.get('button').contains('+ Gericht erstellen').click();
        cy.get('input[placeholder="Deutscher Titel"]').type('TestGericht1234');
        cy.get('input[placeholder="Englischer Titel"]').type('TestDish1234');
        cy.contains('input', 'Speichern').click();
        cy.get('[data-cy="msgClose"]').click();
        cy.get('button').contains('+ Gericht erstellen').click();

        // Filter for the dish
        cy.get('input[placeholder="Suche nach Titel"]').type('TestGericht');

        // Create a dish variation
        cy.get('span')
            .contains('TestGericht1234')
            .parent()
            .parent()
            .contains('Variation erstellen')
            .click();
        cy.get('h3').contains('Variation erstellen');
        cy.get('input[placeholder="Deutscher Titel"]').type('TestVariation1234');
        cy.get('input[placeholder="Englischer Titel"]').type('TestVariation1234');
        cy.contains('input', 'Speichern').click();

        cy.get('span')
            .contains('TestGericht1234')
            .parent()
            .parent()
            .contains('Variation erstellen')
            .click();

        // Verify that the dish variation was created
        cy.get('span').contains('TestVariation1234').should('exist');

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
        cy.get('[data-cy="msgClose"]').click();
        cy.get('span')
            .contains('TestVariation5678')
            .parent()
            .parent()
            .contains('Editieren')
            .click();

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
        cy.get('[data-cy="msgClose"]').click();
        cy.get('span')
            .contains('TestGericht1234')
            .parent()
            .parent()
            .contains('Löschen')
            .click();
        cy.get('[data-cy="msgClose"]').click();

        // Verify that the dish variation was deleted
        cy.get('span').contains('TestVariation5678').should('not.exist');
        cy.get('span').contains('TestGericht1234').should('not.exist');
    });
});