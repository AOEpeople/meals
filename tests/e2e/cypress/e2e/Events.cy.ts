describe('Test Events', () => {
    beforeEach(() => {
        cy.resetDB();
        cy.loginAs('kochomi');
        cy.visitMeals();

        cy.intercept('GET', '**/api/events').as('getEvents');
        cy.intercept('POST', '**/api/events').as('postEvent');
        cy.intercept('PUT', '**/api/events/**').as('putEvent');
        cy.intercept('DELETE', '**/api/events/**').as('deleteEvent');
    });

    it('should be able to switch the locale to english and back to german', () => {
        cy.get('span > a').contains('Events').click({ force: true });
        cy.wait('@getEvents');

        // Check german text
        cy.get('h2').contains('Liste der Events');
        cy.get('input[placeholder="Suche nach Event"]').should('exist');
        cy.contains('button', '+ Event erstellen');
        cy.contains('th', 'Titel');
        cy.contains('th', 'Öffentlich');
        cy.contains('th', 'Aktionen');
        cy.get('span')
            .contains('Afterwork')
            .parent()
            .parent()
            .contains('Editieren');
        cy.get('span')
            .contains('Afterwork')
            .parent()
            .parent()
            .contains('Löschen');

        // Switch language to english
        cy.get('span').contains('English version').parent().click({ force: true });

        // Check english text
        cy.get('h2').contains('List of Events');
        cy.get('input[placeholder="Search for event"]').should('exist');
        cy.contains('button', '+ create Event');
        cy.contains('th', 'Title');
        cy.contains('th', 'Public');
        cy.contains('th', 'Actions');
        cy.get('span')
            .contains('Afterwork')
            .parent()
            .parent()
            .contains('Edit');
        cy.get('span')
            .contains('Afterwork')
            .parent()
            .parent()
            .contains('Delete');

        // Switch language to german
        cy.get('span').contains('Deutsche Version').parent().click({ force: true });

        // Check german text
        cy.get('h2').contains('Liste der Events');
        cy.get('input[placeholder="Suche nach Event"]').should('exist');
        cy.contains('button', '+ Event erstellen');
        cy.contains('th', 'Titel');
        cy.contains('th', 'Öffentlich');
        cy.contains('th', 'Aktionen');
        cy.get('span')
            .contains('Afterwork')
            .parent()
            .parent()
            .contains('Editieren');
        cy.get('span')
            .contains('Afterwork')
            .parent()
            .parent()
            .contains('Löschen');
    });

    it('should create a non-public event', () => {
        cy.get('span > a').contains('Events').click({ force: true });
        cy.wait('@getEvents');

        cy.get('button').contains('+ Event erstellen').click();
        cy.get('h3').contains('Neues Event erstellen');
        cy.get('input[placeholder="Titel"]').type('Test Event 5748');
        cy.get('input').contains('Speichern').click();

        cy.wait('@postEvent');
        cy.wait('@getEvents');
        cy.get('[data-cy="msgClose"]').click();

        cy.get('span').contains('Test Event 5748');
        cy.get('[data-cy="xIcon"]').should('exist');
    });

    it('should create a public event, edit it and delete it', () => {
        cy.get('span > a').contains('Events').click({ force: true });
        cy.wait('@getEvents');

        // Create Event
        cy.get('button').contains('+ Event erstellen').click();
        cy.get('h3').contains('Neues Event erstellen');
        cy.get('input[placeholder="Titel"]').type('Test Event 8563');
        cy.get('button[role="switch"]').click();
        cy.get('input').contains('Speichern').click();

        cy.wait('@postEvent');
        cy.wait('@getEvents');
        cy.get('[data-cy="msgClose"]').click();

        // Verify the event was created
        cy.get('span').contains('Test Event 8563');
        cy.get('[data-cy="checkIcon"]').should('exist');

        cy.get('input[placeholder="Suche nach Event"]').type('Test Event 8563');

        // Edit event
        cy.get('span')
            .contains('Test Event 8563')
            .parent()
            .parent()
            .contains('Editieren')
            .click()
        cy.get('h3').contains('Event editieren');
        cy.get('input[placeholder="Titel"]')
            .should('have.value', 'Test Event 8563')
            .clear()
            .type('Testevent9345');
        cy.get('button[role="switch"]').click();
        cy.get('input').contains('Speichern').click();

        cy.wait('@putEvent');
        cy.get('[data-cy="msgClose"]').click();

        // Verify the event was edited
        cy.get('input[placeholder="Suche nach Event"]')
            .clear()
            .type('Testevent9345');
        cy.get('span').contains('Testevent9345');
        cy.get('[data-cy="xIcon"]').should('exist');

        cy.get('span')
            .parent()
            .parent()
            .contains('Löschen')
            .click()
        cy.wait('@deleteEvent');

        // Verify that the event was deleted
        cy.get('[data-cy="msgClose"]').click();
        cy.get('span').contains('Testevent9345').should('not.exist');
    });
});