describe('Test TV ParticipationsList view', () => {
  beforeEach(() => {
    cy.resetDB();
    cy.setCookie('locale', 'de');
  });

  it('should be able to visit the page without authentication', () => {
    cy.viewport(1080, 1920);
    cy.visit(Cypress.env('baseUrl') + 'show/participations');
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