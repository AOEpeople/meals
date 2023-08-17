export const visitSettlementLinkFromMail = () => {
    cy.request(`${Cypress.env('mailhog_url')}/api/v2/messages?start=0&limit=1`).then((response) => {
        const html: string = response.body.items[0].MIME.Parts[0].Body;
        cy.log(`Body: ${html}`);
        const replaced = html.replace(/(\r\n|\n|\r|=)/gm, "");
        cy.log(`Replaced: ${replaced}`);
        const confirmationURL = replaced.split(Cypress.env('baseUrl'))[1];
        cy.visit(`${Cypress.env('baseUrl')}${confirmationURL}`);
    });
}