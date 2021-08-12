import * as data from "../../fixtures/data.json";
import { login } from "../../support/commands/login";

describe("admin.function.categories.overview", () => {
  const checkComponent = (user: string) => {
    // log user in
    login(user);

    // check navigation for link to 'categories'
    cy.get("ul[class='navbar']")
      .find("a[href='/category']")
      .should("be.visible")
      .and("contain.text", "Categories")
      .as("categories");

    // open categories overview
    cy.get("@categories").click();
    cy.location().should((loc) => {
      expect(loc.pathname).to.contain("/category");
    });

    // check headline
    cy.get("h1[class='headline']")
      .should("be.visible")
      .and("contain.text", "List of categories");

    // check create button
    cy.get("[href='/category/form']")
      .should("be.visible")
      .and("have.text", "Create category");

    // check categories table
    cy.get("table[id='category-table']").should("be.visible").as("table");

    // check table header (2)
    cy.get("@table")
      .find("thead tr th")
      .should("have.length", 2)
      .and("be.visible");

    // check table rows
    cy.get("@table")
      .find("[class*='table-row']")
      .should("have.length.at.least", 1)
      .and("be.visible");

    // check edit buttons
    cy.get("a[class*='edit-form'][href*='/category/form/']")
      .should("have.length.at.least", 1)
      .and("be.visible");

    // check delete buttons
    cy.get("a[class*='button-table'][href*='/delete']")
      .as("deleteAction")
      .should("have.length.at.least", 1)
      .and("be.visible");
  };

  it("is working fine in viewport 'desktop'", () => {
    cy.visitMeals();
    cy.viewportXL();
    checkComponent(data.user.kochomi);
  });
});
