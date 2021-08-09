export const login = (user: string) => {
  // set cookie 'locale' to 'en'
  cy.setCookie("locale", "en");

  // check visibility of elements
  cy.get("header input[name='_username']").should("be.visible").as("name");
  cy.get("header input[name='_password']").should("be.visible").as("password");
  cy.get("header button[type='submit']").should("be.visible").as("submit");

  // log user in
  cy.get("@name").type(`${user}`);
  cy.get("@password").type(`${user}`);
  cy.get("@submit").click();
};
