describe('Test TV ParticipationsList view', () => {
  beforeEach(() => {
    cy.setCookie('locale', 'de');
  });

  it('should be able to visit the page without authentication', () => {
    cy.viewport(1080, 1920);
    cy.visit(Cypress.env('baseUrl') + 'show/participations');
  });

  it('should display vegan/vegetarian icons on some meals', () => {
    cy.intercept('GET', '**/api/print/participations', { fixture: 'tvParticipations.json', statusCode: 200 }).as('getParticipations');
    cy.intercept('GET', '**/api/meals/nextThreeDays', { fixture: 'nextThreeDays.json', statusCode: 200 }).as('getNextThreeDays');
    cy.viewport(1080, 1920);
    cy.visit(Cypress.env('baseUrl') + 'show/participations');

    cy.wait(['@getParticipations', '@getNextThreeDays']);

    // Check the head
    cy.get('th')
      .eq(1)
      .contains('Limbs DE')
      .find('img[data-cy="vegetarian-icon"]')
      .should('exist');

    cy.get('th')
      .first()
      .contains('Innards DE')
      .find('img[data-cy="vegetarian-icon"]')
      .should('not.exist');

    cy.get('th')
      .first()
      .contains('Innards DE')
      .find('img[data-cy="vegan-icon"]')
      .should('not.exist');

    cy.get('th')
      .first()
      .contains('Innards DE')
      .parent()
      .parent()
      .parent()
      .find('tr')
      .eq(1)
      .contains('Innards DE #v1')
      .parent()
      .find('img[data-cy="vegan-icon"]')
      .should('exist');

    cy.get('th')
      .first()
      .contains('Innards DE')
      .parent()
      .parent()
      .parent()
      .find('tr')
      .eq(2)
      .contains('Innards DE #v2')
      .parent()
      .find('img[data-cy="vegetarian-icon"]')
      .should('exist');

    // Check the bottom
    cy.get('table[id="mealsOverview"]')
      .find('th')
      .contains('Montag')
      .parent()
      .parent()
      .parent()
      .find('tbody')
      .find('tr')
      .eq(0)
      .contains('Potatoewrap704DE')
      .parent()
      .find('img[data-cy="vegetarian-icon"]')
      .should('exist');

    cy.get('table[id="mealsOverview"]')
      .find('th')
      .contains('Montag')
      .parent()
      .parent()
      .parent()
      .find('tbody')
      .find('tr')
      .eq(1)
      .contains('Ducksteak751DE')
      .parent()
      .find('img[data-cy="vegetarian-icon"]')
      .should('not.exist');

    cy.get('table[id="mealsOverview"]')
      .find('th')
      .contains('Montag')
      .parent()
      .parent()
      .parent()
      .find('tbody')
      .find('tr')
      .eq(1)
      .contains('Ducksteak751DE')
      .parent()
      .find('img[data-cy="vegan-icon"]')
      .should('not.exist');

    cy.get('table[id="mealsOverview"]')
      .find('th')
      .contains('Dienstag')
      .parent()
      .parent()
      .parent()
      .find('tbody')
      .find('tr')
      .eq(1)
      .contains('Sushipatty624DE')
      .parent()
      .find('img[data-cy="vegan-icon"]')
      .should('exist');

    cy.get('table[id="mealsOverview"]')
      .find('th')
      .contains('Dienstag')
      .parent()
      .parent()
      .parent()
      .find('tbody')
      .find('tr')
      .eq(0)
      .contains('Kebabdumpling968DE')
      .parent()
      .find('img[data-cy="vegan-icon"]')
      .should('not.exist');

    cy.get('table[id="mealsOverview"]')
      .find('th')
      .contains('Dienstag')
      .parent()
      .parent()
      .parent()
      .find('tbody')
      .find('tr')
      .eq(0)
      .contains('Kebabdumpling968DE')
      .parent()
      .find('img[data-cy="vegetarian-icon"]')
      .should('not.exist');
  });
});

describe('Test Print ParticipationList View', () => {
  beforeEach(() => {
    cy.resetDB();
    cy.loginAs('kochomi');
    cy.visitMeals();

    cy.intercept('GET', '**/api/print/participations', { fixture: 'printParticipations.json', statusCode: 200 }).as('getParticipations');
  });

  it('should contain all the infos it received via the fixture', () => {
    cy.contains('span', 'Heutige Teilnehmer').click();

    cy.contains('h1', 'Teilnahmen am Mittwoch, 24.1.');

    cy.get('[data-cy="printTable"]').find('tbody').find('tr').should('have.length', 10);
    cy.contains('th', 'Active w/o limit');
    cy.contains('th', 'Active w/ limit');
    cy.get('[data-cy="printTable"]').find('tbody').find('tr').eq(1).contains('td', 'Meals, Admin');
    cy.get('[data-cy="printTable"]').find('tbody').find('tr').eq(2).contains('td', 'Meals, Alice');
    cy.get('[data-cy="printTable"]').find('tbody').find('tr').eq(3).contains('td', 'Meals, Bob');
    cy.get('[data-cy="printTable"]').find('tbody').find('tr').eq(4).contains('td', 'Meals, Finance');
    cy.get('[data-cy="printTable"]').find('tbody').find('tr').eq(5).contains('td', 'Meals, Jane');
    cy.get('[data-cy="printTable"]').find('tbody').find('tr').eq(6).contains('td', 'Meals, Kochomi');
    cy.get('[data-cy="printTable"]').find('tbody').find('tr').eq(8).contains('td', 'Meals, John');
    cy.get('[data-cy="printTable"]').find('tbody').find('tr').eq(9).find('th').eq(0).contains('Teilnahmen');
    cy.get('[data-cy="printTable"]').find('tbody').find('tr').eq(9).find('th').eq(1).contains('7');
    cy.get('[data-cy="printTable"]').find('tbody').find('tr').eq(9).find('th').eq(2).contains('4');
    cy.get('[data-cy="printTable"]').find('tbody').find('tr').eq(9).find('th').eq(3).contains('0');

    cy.get('p').contains('Pdf downloaden');
  });

  it('should verify that a file is downloaded when clicking pdf download', () => {
    cy.contains('span', 'Heutige Teilnehmer').click();

    cy.contains('h1', 'Teilnahmen am Mittwoch, 24.1.');

    cy.get('p').contains('Pdf downloaden').click();

    cy.readFile('cypress/downloads/teilnehmerliste.pdf', { timeout: 5000 });
  });
});