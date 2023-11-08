describe('Test Weeks View', () => {
    beforeEach(() => {
        cy.resetDB();
        cy.loginAs('kochomi');
        cy.visitMeals();
    });

    it('should contain the correct heading in german and english', () => {
        cy.get('span > a').contains('Mahlzeiten').click();

        cy.get('h2').should(ele => {
            expect(ele.first()).to.contain('Liste der Wochen');
        });

        cy.get('span').contains('English version').parent().click();

        cy.get('h2').should(ele => {
            expect(ele.first()).to.contain('List of Weeks');
        });
    });

    it('should contain eight weeks', () => {
        cy.get('span > a').contains('Mahlzeiten').click();

        cy.get('h4').should('have.length', 8);
        cy.get('h4').each((ele) => {
            expect(ele.text()).to.contain('Woche #');
        });
    });
});