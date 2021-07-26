import * as data from "../../fixtures/data.json";
import { login } from "../../support/commands/login";

describe("admin.function.menu.overview", () => {
  const checkComponent = (user: string) => {
    // log user in
    login(user);

    // check visibility of elements
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

    // check visibility of elements
    cy.get("h1[class='headline']")
      .should("be.visible")
      .and("contain.text", "List of weeks");

    // at least one week should have meals
    cy.get("[class='week']").should("have.length.at.least", 1);

    // at least one week should have no meals
    cy.get("[class='week week-create']").should("have.length.at.least", 1);
  };

  it("is working fine in viewport 'desktop'", () => {
    cy.visitMeals();
    cy.viewportXL();
    checkComponent(data.user.kochomi);
  });
});
