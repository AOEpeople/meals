import * as data from "../../fixtures/data.json";
import { login } from "../../support/commands/login";

describe("admin.function.menu.overview", () => {
  const checkComponent = (user: string) => {
    // log user in
    login(user);

    // check navigation for link to 'menu'
    cy.get("ul[class='navbar']")
      .find("a[href='/menu']")
      .should("be.visible")
      .and("contain.text", "Menu")
      .as("menu");

    // open menu overview
    cy.get("@menu").click();
    cy.location().should((loc) => {
      expect(loc.pathname).to.contain("/menu");
    });

    // check headline
    cy.get("h1[class='headline']")
      .should("be.visible")
      .and("contain.text", "List of weeks");

    // check weeks list
    cy.get("[class*='week-list']").should("be.visible").as("list");

    // check weeks with meals
    cy.get("@list")
      .find("[class='week']")
      .should("have.length.at.least", 1)
      .and("be.visible");

    // check weeks without meals
    cy.get("@list")
      .find("[class='week week-create']")
      .should("have.length.at.least", 1)
      .and("be.visible");
  };

  it("is working fine in viewport 'desktop'", () => {
    cy.visitMeals();
    cy.viewportXL();
    checkComponent(data.user.kochomi);
  });
});
