import * as data from "../../fixtures/data.json";
import { login } from "../../support/commands/login";

describe("admin.function.dishes.overview", () => {
  const checkComponent = (user: string) => {
    // log user in
    login(user);

    // check visibility of elements
    cy.get("ul[class='navbar']")
      .find("a[href='/dish']")
      .should("be.visible")
      .and("contain.text", "Dishes")
      .as("dishes");

    // open dishes overview
    cy.get("@dishes").click();
    cy.location().should((loc) => {
      expect(loc.pathname).to.contain("/dish");
    });

    // check visibility of elements
    cy.get("h1[class='headline']")
      .should("be.visible")
      .and("contain.text", "List of dishes");
    cy.get("[href='/dish/form']")
      .should("be.visible")
      .and("have.text", "Create dish");

    // check table
    cy.get("table[id='dish-table']").should("be.visible");
  };

  it("is working fine in viewport 'desktop'", () => {
    cy.visitMeals();
    cy.viewportXL();
    checkComponent(data.user.kochomi);
  });
});
