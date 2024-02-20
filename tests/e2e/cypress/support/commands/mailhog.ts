export const visitSettlementLinkFromMail = (times: number = 0) => {
    cy.wait(500);
    cy.request({ url: `${Cypress.env('mailhog_url')}/api/v2/messages?start=0&limit=1`, failOnStatusCode: false }).then((response) => {
        cy.log('Checking response of mailhog api v2...');
        cy.log(`V2:: Times: ${times}, Response: ${JSON.stringify(response)}`);
        cy.wait(1000);
        if (response.status === 200) {
            const html = response.body;
            cy.log(`V2:: Body: ${JSON.stringify(html)}`);
            const items = html.items[0];
            cy.log(`V2:: items: ${JSON.stringify(items)}`);
            if (times < 3 && (items.MIME === undefined || items.MIME === null)) {
                cy.log('V2:: retrying getting settling mail');
                visitSettlementLinkFromMail(times + 1);
            } else {
                const mime = items.MIME;
                cy.log(`V2:: mime: ${JSON.stringify(mime)}`);
                const body = mime.Parts[0].Body;
                cy.log(`V2:: mime body: ${JSON.stringify(body)}`);
                const replaced = body.replace(/(\r\n|\n|\r|=)/gm, "");
                cy.log(`V2:: Replaced: ${JSON.stringify(replaced)}`);
                const confirmationURL = replaced.split(Cypress.env('baseUrl'))[1];
                cy.log(`V2:: confimartion URL: ${confirmationURL}`);
                cy.visit(`${Cypress.env('baseUrl')}${confirmationURL}`);
            }
        } else {
            cy.log('Checking response of mailhog api v1, because v2 is not responding');
            cy.request({ url: `${Cypress.env('mailhog_url')}/api/v1/messages?start=0&limit=1`, failOnStatusCode: false }).then((response) => {
                cy.log(`V1:: Times: ${times}, Response: ${JSON.stringify(response)}`);
                cy.wait(1000);
                if (response.status !== 200) {
                    cy.log('V1:: retrying getting settling mail');
                    visitSettlementLinkFromMail(times + 1);
                } else {
                    const html = response.body;
                    cy.log(`V1:: Body: ${JSON.stringify(html)}`);
                    const message = html.messages[0].Snippet;
                    cy.log(`V1:: message: ${JSON.stringify(message)}`);
                    const replaced = message.replace(/(\r\n|\n|\r|=)/gm, "");
                    cy.log(`V1:: Replaced: ${JSON.stringify(replaced)}`);
                    const confirmationURL = replaced.split(Cypress.env('baseUrl'))[1];
                    cy.log(`V1:: confimartion URL: ${confirmationURL}`);
                    cy.visit(`${Cypress.env('baseUrl')}${confirmationURL}`);
                }
            });
        }
    });

}