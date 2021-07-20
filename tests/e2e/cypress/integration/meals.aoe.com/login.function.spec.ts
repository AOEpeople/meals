import * as data from "../../fixtures/data.json";

describe("login.function", () => {
  const checkComponent = (user: String) => {
    // check basic login elements
    cy.get("header").should("be.visible");
    cy.get("header a[class='logo']").should("be.visible");

    cy.get("header input[name='_username']").should("be.visible").as("name");
    cy.get("header input[name='_password']")
      .should("be.visible")
      .as("password");
    cy.get("header button[type='submit']").should("be.visible").as("submit");
    cy.get("header a[class='language-switch']").should("be.visible");

    // log user in
    cy.get("@name").type(`${user}`);
    cy.get("@password").type(`${user}`);
    cy.get("@submit").click();

    // check login state
    cy.get("header div[class='login-text']")
      .should("contain.text", "You are logged in as: ")
      .and("contain.text", `${user}`);

    cy.get("header a[href='/language-switch']").should("be.visible");
    cy.get("header a[href='/logout']").should("be.visible").as("logout");

    cy.get("header input[name='_username']").should("not.exist");
    cy.get("header input[name='_password']").should("not.exist");
    cy.get("header button[type='submit']").should("not.exist");

    // log user out
    cy.get("@logout").click();

    // check logout state
    cy.get("@name").should("be.visible");
    cy.get("@password").should("be.visible");
    cy.get("@submit").should("be.visible");
  };

  it("is working fine in viewport 'desktop'", () => {
    cy.visitMeals();
    cy.viewportXL();
    checkComponent(data.user.bob);
  });

  it("is working fine in viewport 'desktop'", () => {
    cy.visitMeals();
    cy.viewportXL();
    checkComponent(data.user.kochomi);
  });
});
