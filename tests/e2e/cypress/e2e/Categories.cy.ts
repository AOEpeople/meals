describe('Test Categories View', () => {
    beforeEach(() => {
        cy.resetDB();
        cy.loginAs('kochomi');
        cy.visitMeals();
    });

    it("should be able to navigate to '/categories' and have the header displayed", () => {
        cy.get('span > a').contains('Kategorie').click();

        cy.get('h2').should(ele => {
            expect(ele.first()).to.contain('Liste der Kategorien');
        });

        cy.contains('button', '+ Kategorie erstellen');
    });

    it('should be able to switch the locale to english and back to german', () => {
        cy.get('span > a').contains('Kategorie').click();

        // Switch language to english
        cy.get('span').contains('English version').parent().click();

        // Check wether text has switched to english
        cy.get('h2').should(ele => {
            expect(ele.first()).to.contain('List of Categories');
        });
        cy.contains('button', '+ create category');
        cy.contains('p', 'Edit');
        cy.contains('p', 'Delete');
        cy.contains('th', 'Actions');
        cy.contains('th', 'Title');

        // Switch language back to german
        cy.get('span').contains('Deutsche Version').parent().click();

        // Check wether text has switched to german
        cy.get('h2').should(ele => {
            expect(ele.first()).to.contain('Liste der Kategorien');
        });
        cy.contains('button', '+ Kategorie erstellen');
        cy.contains('p', 'Editieren');
        cy.contains('p', 'Löschen');
        cy.contains('th', 'Aktionen');
        cy.contains('th', 'Titel');
    });

    it('should be able to create, edit and delete a category', () => {
        cy.get('span > a').contains('Kategorie').click();

        // Create Category
        cy.get('button').contains('+ Kategorie erstellen').click();
        cy.contains('h3', 'Neue Kategorie erstellen');
        cy.get('input[placeholder="Deutscher Titel"]').type('TestKategorie1234');
        cy.get('input[placeholder="Englischer Titel"]').type('TestCategory1234');
        cy.contains('input', 'Speichern').click();
        cy.get('[data-cy="msgClose"]').click();

        // Verify that the category was created
        cy.get('span').contains('TestKategorie1234');

        // Edit Category
        cy.get('span')
            .contains('TestKategorie1234')
            .parent()
            .parent()
            .contains('p', 'Editieren')
            .click();
        cy.contains('h3', 'Kategorie editieren');
        cy.get('input[placeholder="Deutscher Titel"]').clear().type('TestKategorie5678');
        cy.get('input[placeholder="Englischer Titel"]').clear().type('TestKategory5678');
        cy.contains('input', 'Speichern').click();
        cy.get('[data-cy="msgClose"]').click();

        // Verify that the category was successfully edited
        cy.get('span')
            .contains('TestKategorie5678')
            .parent()
            .parent()
            .contains('p', 'Editieren')
            .click();

        // Delete Category
        cy.get('span')
            .contains('TestKategorie5678')
            .parent()
            .parent()
            .contains('p', 'Löschen')
            .click();

        cy.get('[data-cy="msgClose"]').click();

        // Verify that the Category was successfully deleted
        cy.get('span').contains('TestKategorie5678').should('not.exist');
    });
});