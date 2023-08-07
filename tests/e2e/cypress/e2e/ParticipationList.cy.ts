describe('Test ParticipationsList view', () => {
  beforeEach(() => {
    cy.setCookie('locale', 'de');
    cy.resetDB();
  });

  it('should be able to visit the page without authentication', () => {
    cy.viewport(1080, 1920);
    cy.visit('/show/participations');
  });
});