describe('Test ParticipationsList view', () => {
  beforeEach(() => {
    cy.resetDB();
    cy.setCookie('locale', 'de');
  });

  it('should be able to visit the page without authentication', () => {
    cy.viewport(1080, 1920);
    cy.visit('http://localhost/show/participations');
  });
});