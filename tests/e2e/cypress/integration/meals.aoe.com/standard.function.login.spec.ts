import * as data from "../../fixtures/data.json";
import { login } from "../../support/commands/login";

describe("standard.function.login", () => {
  const checkComponent = (user: string) => {
    // check visibility of elements
    cy.get("header").should("be.visible");
    cy.get("header a[class='logo']").should("be.visible");

    // log user in
    login(user);
    cy.get("header a[class='language-switch']").should("be.visible");

    // check login state
    cy.get("header div[class='login-text']")
      .should("contain.text", "You are logged in as:")
      .and("contain.text", `${user}`);
    cy.get("header a[href='/language-switch']").should("be.visible");
    cy.get("header a[href='/logout']").should("be.visible").as("logout");

    cy.get("header input[name='_username']").should("not.exist");
    cy.get("header input[name='_password']").should("not.exist");
    cy.get("header button[type='submit']").should("not.exist");

    // check weeks (2)
    cy.get("[class='weeks']")
      .should("be.visible")
      .find("[class='week']")
      .should("have.length", 2)
      .and("be.visible")
      .first()
      .as("week");

    // check one week
    cy.get("@week")
      .find("h1[class='headline']")
      .should("be.visible")
      .and("contain.text", "Current week");

    // check meals of this week (5)
    cy.get("@week")
      .find("[class*='meal ']")
      .should("have.length", 5)
      .and("be.visible");

    // log user out
    cy.get("@logout").click();

    // check logout state
    cy.get("header input[name='_username']").should("be.visible");
    cy.get("header input[name='_password']").should("be.visible");
    cy.get("header button[type='submit']").should("be.visible");
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
