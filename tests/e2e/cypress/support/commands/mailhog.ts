export const visitSettlementLinkFromMail = () => {
    cy.wait(500);
    cy.request(`${Cypress.env('mailhog_url')}/api/v1/messages?start=0&limit=1`).then((response) => {
        cy.log('Checking response of mailhog...');
        cy.wait(1000);
        expect(response.status).to.eq(200);
        const html = response.body;
        cy.log(`Body: ${html}`);
        const message = html.messages[0].Snippet;
        const replaced = message.replace(/(\r\n|\n|\r|=)/gm, "");
        cy.log(`Replaced: ${replaced}`);
        const confirmationURL = replaced.split(Cypress.env('baseUrl'))[1];
        cy.visit(`${Cypress.env('baseUrl')}${confirmationURL}`);
    });
}