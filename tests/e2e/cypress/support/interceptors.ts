export const setCookieInterceptor = () => {
    // see: https://github.com/cypress-io/cypress/issues/9347
    cy.intercept(`${Cypress.env('baseUrl')}**`, (req) => {
        req.on('response', (res) => {
            let cookies = res.headers['set-cookie'];
            if (!Array.isArray(cookies)) {
                return;
            }
            // figure out how many session cookies are available
            let sessionCookieIndexes = [];
            for (let idx in cookies) {
                if (cookies[idx].startsWith('PHPSESSID=')) {
                    sessionCookieIndexes.push(idx);
                }
            }
            // do nothing if only one/no session cookie
            if (2 > sessionCookieIndexes.length) {
                return;
            }
            // remove expired session cookie
            for (let idx in sessionCookieIndexes) {
                if (cookies[idx].startsWith('PHPSESSID=deleted')) {
                    cookies = cookies.splice(idx, 1);
                    return;
                }
            }
        });
    });
};
