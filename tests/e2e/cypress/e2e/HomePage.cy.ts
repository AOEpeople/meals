describe('Test Base URL and login', () => {

  beforeEach(() => {
    cy.resetDB();
    cy.loginAs('kochomi');
    cy.visitMeals();
  });

  it('should be able to visit the page and login', () => {
    cy.visitMeals();
    cy.contains(/Aktuelle Woche/);
    cy.contains(/Nächste Woche/);
  });
});