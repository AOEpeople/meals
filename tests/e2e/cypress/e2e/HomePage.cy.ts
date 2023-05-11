describe('Test Base URL and login', () => {

  beforeEach(() => {
    cy.setCookie('locale', 'en');
    cy.loginAs('kochomi');
    cy.visitMeals();
  });

  it('should be able to visit the page and login', () => {
    cy.visit('/');
    cy.contains(/Aktuelle Woche/);
    cy.contains(/Nächste Woche/);
  });
});