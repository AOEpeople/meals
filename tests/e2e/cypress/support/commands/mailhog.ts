export const visitSettlementLinkFromMail = (times: number = 0) => {
    cy.wait(500);
    cy.request(`${Cypress.env('mailhog_url')}/api/v2/messages?start=0&limit=1`).then((response) => {
        cy.log('Checking response of mailhog...');
        cy.wait(1000);
        expect(response.status).to.eq(200);
        const html = response.body;
        cy.log(`Body: ${html}`);
        const items = html.items[0];
        cy.log(`items: ${items}`);
        if (times < 3 && (items.MIME === undefined || items.MIME === null)) {
            cy.log('retrying getting settling mail');
            visitSettlementLinkFromMail(times + 1);
        } else {
            const mime = items.MIME;
            const body = mime.Parts[0].Body;
            const replaced = body.replace(/(\r\n|\n|\r|=)/gm, "");
            cy.log(`Replaced: ${replaced}`);
            const confirmationURL = replaced.split(Cypress.env('baseUrl'))[1];
            cy.visit(`${Cypress.env('baseUrl')}${confirmationURL}`);
        }
    });
}