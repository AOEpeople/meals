export const resetDB = () => {
    cy.exec('cd ./../.. && make load-testdata', { log: true, failOnNonZeroExit: true }).its('code').should('eq', 0);
}