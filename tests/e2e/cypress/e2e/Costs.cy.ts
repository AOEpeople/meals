describe('Test Cost View', () => {
    beforeEach(() => {
        cy.resetDB();
        cy.loginAs('kochomi');
        cy.visitMeals();

        // spy on the request to the backend to wait for them to resolve before testing
        cy.intercept('GET', '**/api/costs').as('getCosts');
        cy.intercept('POST', '**/api/costs/hideuser/**').as('hideUser');
        cy.intercept('POST', '**/api/costs/settlement/confirm/**').as('confirmSettlement');
        cy.intercept('POST', '**/api/payment/cash/**').as('postBalance');
    });

    it('should visit /costs and filter for a user and hide it', () => {
        cy.get('span > a').contains('Kosten').click({ force: true });

        cy.wait('@getCosts');

        cy.get('h2').contains('Liste der Kosten');

        cy.get('input[placeholder="Benutzer filtern"]').type('Admin');

        // find the test user and assert that the table has the right dimensions
        cy.get('[data-cy="costsTable"]')
            .find('tr')
            .eq(1)
            .find('td')
            .eq(0)
            .contains('Meals, Admin')
            .parent()
            .find('td')
            .should('have.length', 8);

        cy.get('[data-cy="costsTable"]')
            .find('tr')
            .eq(1)
            .find('td')
            .eq(7)
            .find('button')
            .first()
            .click();

        cy.wait('@hideUser');

        cy.contains('Meals, Admin')
            .should('not.exist');

        cy.get('input').first().clear();

        cy.get('tr:visible').should('have.length', 57);

        cy.get('label').contains('Ausgeblendete Benutzer anzeigen').click();

        cy.get('tr:visible').should('have.length', 58);
    });

    it('should be able to settle an account', () => {
        cy.get('span > a').contains('Kosten').click();

        cy.wait('@getCosts');

        cy.get('h2').contains('Liste der Kosten');

        cy.get('input[placeholder="Benutzer filtern"]').type('Alice');

        cy.get('[data-cy="costsTable"]')
            .find('tr')
            .eq(1)
            .find('td')
            .eq(0)
            .contains('Meals, Alice')
            .parent()
            .find('td')
            .should('have.length', 8);

        cy.get('[data-cy="costsTable"]')
            .find('tr')
            .eq(1)
            .find('td')
            .eq(7)
            .find('button')
            .eq(1)
            .click();

        cy.get('form')
            .find('input')
            .first()
            .clear({ force: true })
            .type('1234', { force: true });

        cy.get('form')
            .find('input[value="Speichern"]')
            .click({ force: true });

        cy.get('[data-cy="costsTable"]')
            .find('tr')
            .eq(1)
            .find('td')
            .eq(7)
            .find('button')
            .last()
            .click();

        cy.get('div').contains('Fortfahren').click();

        cy.visitSettlementLinkFromMail();
        cy.get('div').contains('Bestätigen').click();
        cy.wait('@confirmSettlement');

        cy.get('span > a').contains('Kosten').click();
        cy.wait('@getCosts');

        cy.get('input[placeholder="Benutzer filtern"]').type('Meals, Alice');

        cy.get('[data-cy="costsTable"]')
            .find('tr')
            .should('have.length', 1);
    });

    it('should be able to add balance to a user', () => {
        cy.get('span > a').contains('Kosten').click();

        cy.wait('@getCosts');

        cy.get('h2').contains('Liste der Kosten');

        cy.get('input[placeholder="Benutzer filtern"]').type('Alice');

        cy.get('[data-cy="costsTable"]')
            .find('tr')
            .eq(1)
            .find('td')
            .eq(0)
            .contains('Meals, Alice')
            .parent()
            .find('td')
            .should('have.length', 8);

        cy.get('[data-cy="costsTable"]')
            .find('tr')
            .eq(1)
            .find('td')
            .eq(0)
            .contains('Meals, Alice')
            .parent()
            .find('td')
            .eq(6)
            .invoke('text')
            .as('cost');

        cy.get<string>('@cost').then((cost) => {
            const parsedCost = parseFloat(parseFloat(cost.replace(' €', '').replace('.', '').replace(',', '.')).toFixed(2));
            const balanceToAdd: number = 147.00
            const balanceToSearch: number = parsedCost + balanceToAdd
            const searchString: string = `${balanceToSearch.toLocaleString()} €`;


            cy.get('[data-cy="costsTable"]')
                .find('tr')
                .eq(1)
                .find('td')
                .eq(7)
                .find('button')
                .eq(1)
                .click();

            cy.get('form')
                .find('input')
                .first()
                .clear({ force: true })
                .type('147', { force: true });

            cy.get('form')
                .find('input[value="Speichern"]')
                .click({ force: true });

            cy.wait('@postBalance');
            cy.get('h2')
                .contains('Liste der Kosten')
                .click();

            cy.get('[data-cy="costsTable"]')
                .find('tr')
                .eq(1)
                .find('td')
                .eq(6)
                .contains(searchString);
        })
    })
});