export const resetDB = () => {
    if (Cypress.env('ddev_test')) {
        cy.exec('cd ./../.. && make load-testdata', { log: true, failOnNonZeroExit: true }).its('exitCode').should('eq', 0);
    } else {
        cy.exec('docker exec app bin/console doctrine:fixtures:load -n', { log: true, failOnNonZeroExit: true }).its('exitCode').should('eq', 0);
    }
}