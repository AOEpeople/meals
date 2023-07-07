describe('Test Weeks View', () => {
    beforeEach(() => {
        cy.setCookie('locale', 'de');
        cy.loginAs('kochomi');
        cy.visitMeals();
    });

    it('should contain the correct heading in german and english', () => {
        cy.visit('/weeks');

        cy.get('h2').should(ele => {
            expect(ele.first()).to.contain('Liste der Wochen');
        });

        cy.get('span').contains('English version').parent().click();

        cy.get('h2').should(ele => {
            expect(ele.first()).to.contain('List of Weeks');
        });
    });

    it('should contain eight weeks', () => {
        cy.visit('/weeks');

        cy.get('h4').should('have.length', 8);
        cy.get('h4').each((ele) => {
            expect(ele.text()).to.contain('Woche #');
        });
    });
});