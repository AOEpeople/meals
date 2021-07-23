import * as data from "../../fixtures/data.json";
import { login } from "../../support/commands/login";

describe("admin.function.categories.overview", () => {
  const checkComponent = (user: string) => {
    // log user in
    login(user);

    // check visibility of elements
    cy.get("ul[class='navbar']")
      .find("a[href='/category']")
      .should("be.visible")
      .and("contain.text", "Categories")
      .as("categories");

    // open categories
    cy.get("@categories").click();
    cy.location().should((loc) => {
      expect(loc.pathname).to.contain("/category");
    });

    // check visibility of elements
    cy.get("h1[class='headline']")
      .should("be.visible")
      .and("contain.text", "List of categories");
    cy.get("[href='/category/form']")
      .should("be.visible")
      .and("have.text", "Create category");

    // check table
    cy.get("table[id='category-table']").should("be.visible");
  };

  it("is working fine in viewport 'desktop'", () => {
    cy.visitMeals();
    cy.viewportXL();
    checkComponent(data.user.kochomi);
  });
});
