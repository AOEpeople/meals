export const visitSettlementLinkFromMail = (times: number = 0) => {
    cy.wait(500);
    cy.request({ url: `${Cypress.env('mailhog_url')}/api/v2/messages?start=0&limit=1`, failOnStatusCode: false }).then((response) => {
        cy.log('Checking response of mailhog api v2...');
        cy.wait(1000);
        if (response.status === 200) {
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
        }
    });

    cy.log('Checking response of mailhog api v1, because v2 is not responding');
    cy.request({ url: `${Cypress.env('mailhog_url')}/api/v1/messages?start=0&limit=1`, failOnStatusCode: false }).then((response) => {
        cy.wait(1000);
        if (response.status !== 200) {
            cy.log('retrying getting settling mail');
            visitSettlementLinkFromMail(times + 1);
        } else {
            const html = response.body;
            cy.log(`Body: ${html}`);
            const message = html.messages[0].Snippet;
            const replaced = message.replace(/(\r\n|\n|\r|=)/gm, "");
            cy.log(`Replaced: ${replaced}`);
            const confirmationURL = replaced.split(Cypress.env('baseUrl'))[1];
            cy.visit(`${Cypress.env('baseUrl')}${confirmationURL}`);
        }
    });
}