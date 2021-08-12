import * as data from "../../fixtures/data.json";
import { login } from "../../support/commands/login";

describe("admin.function.dishes.overview", () => {
  const checkComponent = (user: string) => {
    // log user in
    login(user);

    // check navigation for link to 'dishes'
    cy.get("ul[class='navbar']")
      .get("a[href='/dish']")
      .should("be.visible")
      .and("contain.text", "Dishes")
      .as("dishes");

    // open dishes overview
    cy.get("@dishes").click();
    cy.location().should((loc) => {
      expect(loc.pathname).to.contain("/dish");
    });

    // check headline
    cy.get("h1[class='headline']")
      .should("be.visible")
      .and("contain.text", "List of dishes");

    // check create button
    cy.get("[href='/dish/form']")
      .should("be.visible")
      .and("have.text", "Create dish");

    // check dishes table
    cy.get("table[id='dish-table']").should("be.visible").as("table");

    // check table rows
    cy.get("@table")
      .find("[class*='table-row']")
      .should("have.length.at.least", 1)
      .should("be.visible");

    // check add variation buttons
    cy.get("a[class*='edit-form'][href*='/variation/new']")
      .should("have.length.at.least", 1)
      .should("be.visible");

    // check edit buttons
    cy.get("a[class*='edit-form'][href^='/dish/form/']")
      .should("have.length.at.least", 1)
      .should("be.visible");

    // check delete buttons
    cy.get("a[class*='button-table'][href*='/delete']")
      .should("have.length.at.least", 1)
      .should("be.visible");
  };

  it("is working fine in viewport 'desktop'", () => {
    cy.visitMeals();
    cy.viewportXL();
    checkComponent(data.user.kochomi);
  });
});
