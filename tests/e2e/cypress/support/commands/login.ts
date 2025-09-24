export const login = (username: string, password: string) => {
  logout();
  const loginFn = Cypress.env('oauth_enable') ? oauthLogin : simpleLogin;
  loginFn(username, password);
};

export const loginAs = (user: string) => {
  cy.fixture(`users/${user}.json`).then((credentials) => {
    login(credentials.username, credentials.password);
  });

};

export const logout = () => {
  Cypress.env('oauth_enable') ? oauthLogout() : simpleLogout();
};

const oauthLogin = (username: string, password: string) => {
  cy.login({
    root: Cypress.env('oauth_base_url'),
    realm: Cypress.env('oauth_realm'),
    username: username,
    password: password,
    client_id: Cypress.env('oauth_client_id'),
    redirect_uri: Cypress.config("baseUrl") + Cypress.env("oauth_redirect_uri")
  });
}

const oauthLogout = () => {
  cy.request({
    url: `${Cypress.env('oauth_base_url')}/auth/realms/${Cypress.env('oauth_realm')}/protocol/openid-connect/logout`,
    followRedirect: false
  });
};

const simpleLogin = (username: string, password: string) => {
  cy.visit('/login');
  cy.get("#username").type(username);
  cy.get("#password").type(password);
  cy.get("#password").closest("form").submit()
}

const simpleLogout = () => {
  cy.visit('/logout');
};

